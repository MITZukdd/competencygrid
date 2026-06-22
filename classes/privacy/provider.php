<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_competencygrid\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use mod_competencygrid\local\repository\userinput_repository;
use core_privacy\local\request\writer;


/**
 * Class provider
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {
    /**
     * Get the metadata about this system.
     *
     * @param collection $collection The collection to add information to.
     * @return collection The collection containing the metadata.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'competencygrid_userinput',
            [
                    'userid' => 'privacy:metadata:competencygrid_userinput:userid',
                    'gridid' => 'privacy:metadata:competencygrid_userinput:gridid',
                    'skillid' => 'privacy:metadata:competencygrid_userinput:skillid',
                    'subskillid' => 'privacy:metadata:competencygrid_userinput:subskillid',
                    'levelniveau' => 'privacy:metadata:competencygrid_userinput:levelniveau',
                    'timecreated' => 'privacy:metadata:competencygrid_userinput:timecreated',
                ],
            'privacy:metadata:competencygrid_userinput'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     * Most of this code was adapted from https://moodledev.io/docs/5.2/apis/subsystems/privacy
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                INNER JOIN {competencygrid} cg ON cg.id = cm.instance
                ";

        $params = [
                'modname'           => 'competencygrid',
                'contextlevel'      => CONTEXT_MODULE,
                'userid'  => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }
            $gridid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

            helper::export_context_files($context, $contextlist->get_user());

            $structure = self::build_export_structure($userid, $gridid);

            if (empty($structure)) {
                continue;
            }

            foreach ($structure as $index => $skill) {
                foreach ($skill['subskills'] as $subindex => $subskill) {
                    $subcontext = [
                           self::sanitize($DB->get_field('competencygrid', 'name', ['id' => $gridid])),
                            $index . self::sanitize($skill['name']),
                            $subindex . self::sanitize($subskill['name']),
                    ];

                    $exportdata = (object) [
                            'subskillname' => $subskill['name'],
                            'evaluations' => $subskill['evaluations'],
                    ];

                    writer::with_context($context)->export_data($subcontext, $exportdata);
                }
            }
        }
    }

    /**
     * Delete all user data for the specified context.
     *
     * @param context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('competencygrid', $context->instanceid);
        if (!$cm) {
            return;
        }

        $moduleinstance = $DB->get_record('competencygrid', ['id' => $cm->instance], '*', MUST_EXIST);
        $userinputrepo = new userinput_repository();
        $userinputrepo->delete_userinputs_by_gridid($moduleinstance->id);
    }

    /**
     * Delete all user data for the specified user of the contextlist.
     *
     * @param approved_contextlist $contextlist The approved contexts to delete data for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userinputrepo = new userinput_repository();

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            $gridid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $userinputrepo->delete_userinputs_by_userid_gridid($userid, $gridid);
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist to add users to.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('competencygrid', $context->instanceid);
        if (!$cm) {
            return;
        }
        $moduleinstance = $DB->get_record('competencygrid', ['id' => $cm->instance], '*', MUST_EXIST);
        $gridid = $moduleinstance->id;

        $userids = $DB->get_fieldset('competencygrid_userinput', 'userid', ['gridid' => $gridid]);

        $userlist->add_users($userids);
    }

    /**
     * Delete all user data for the specified users.
     *
     * @param approved_userlist $userlist The approved users to delete data for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        $grid = $DB->get_record('competencygrid', ['id' => $cm->instance]);

        $userinputrepo = new userinput_repository();

        foreach ($userlist->get_users() as $user) {
            $userinputrepo->delete_userinputs_by_userid_gridid($user->id, $grid->id);
        }
    }

    /**
     * Helper method to build the nested structure for export.
     *
     * @return array Nested array: [skillid => ['name' => ..., 'subskills' => [...]]]
     */
    private static function build_export_structure(int $userid, int $gridid): array {
        global $DB;

        // Skills.
        $skills = $DB->get_records(
            'competencygrid_skills',
            ['gridid' => $gridid],
            'id ASC',
            'id, name'
        );

        if (empty($skills)) {
            return [];
        }

        // Subskills.
        [$insql, $params] = $DB->get_in_or_equal(array_keys($skills));
        $subskills = $DB->get_records_select(
            'competencygrid_subskills',
            "skillid $insql",
            $params,
            'skillid ASC, id ASC',
            'id, skillid, name'
        );

        if (empty($subskills)) {
            return [];
        }

        // User inputs.
        $userinputs = $DB->get_records(
            'competencygrid_userinput',
            ['userid' => $userid, 'gridid' => $gridid],
            'subskillid ASC, timemodified ASC',
            'id, subskillid, levelniveau, timemodified'
        );

        if (empty($userinputs)) {
            return [];  // User has no data - nothing to export.
        }

        // Group user inputs by subskill.
        $inputsbyskill = [];
        foreach ($userinputs as $input) {
            $inputsbyskill[$input->subskillid][] = (object) [
                    'levelniveau' => (int) $input->levelniveau,
                    'timemodified' => date('Y-m-d H:i:s', $input->timemodified),
            ];
        }

        // Group subskills by skill.
        $subskillsbyskill = [];
        foreach ($subskills as $subskill) {
            if (!isset($inputsbyskill[$subskill->id])) {
                continue;  // User has no inputs for this subskill : skip.
            }
            $subskillsbyskill[$subskill->skillid][] = [
                    'id' => (int) $subskill->id,
                    'name' => $subskill->name,
                    'evaluations' => $inputsbyskill[$subskill->id],
            ];
        }

        // Build final nested structure (only skills with at least one evaluated subskill).
        $result = [];
        foreach ($skills as $skill) {
            if (empty($subskillsbyskill[$skill->id])) {
                continue;
            }
            $result[$skill->id] = [
                    'id' => (int) $skill->id,
                    'name' => $skill->name,
                    'subskills' => $subskillsbyskill[$skill->id],
            ];
        }

        return $result;
    }

    /**
     * Helper method to sanitize context names for export.
     *
     * @param string $name The name to sanitize.
     * @return string The sanitized name.
     */
    private static function sanitize(string $name) {
        if (mb_strlen($name) > 15) {
            $name = mb_substr($name, 0, 14);
        }
        $name = str_replace(['/', '\\', '.'], '-', $name);
        $name = preg_replace('/\s+/', '-', $name);
        $name = trim($name);
        $name = trim($name, '-');
        return $name;
    }
}
