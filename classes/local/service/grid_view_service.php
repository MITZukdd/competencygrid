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

namespace mod_competencygrid\local\service;
use context_course;
use context_user;
use core_course\external\course_summary_exporter;
use core_course_category;
use core_favourites\service_factory;
use mod_competencygrid\local\repository\coursereference_repository;
use mod_competencygrid\local\repository\leveldefinition_repository;
use mod_competencygrid\local\repository\levelinstance_repository;
use mod_competencygrid\local\repository\skill_repository;
use mod_competencygrid\local\repository\subskill_repository;
use mod_competencygrid\local\repository\userinput_repository;
use mod_competencygrid\output\competencygrid_inplace_editable_leveldefinition;
use mod_competencygrid\output\competencygrid_inplace_editable_levelinstance;
use mod_competencygrid\output\competencygrid_inplace_editable_skill;
use mod_competencygrid\output\competencygrid_inplace_editable_subskill;
use moodle_url;

/**
 * Class grid_view_service. Aggregates and prepares data from different repositories for rendering.
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grid_view_service {

    /** @var skill_repository */
    private skill_repository $skillrepository;

    /** @var subskill_repository */
    private subskill_repository $subskillrepository;

    /** @var userinput_repository */
    private userinput_repository $userinputrepository;

    /** @var leveldefinition_repository */
    private leveldefinition_repository $leveldefinitionrepository;

    /** @var levelinstance_repository */
    private levelinstance_repository $levelinstancerepository;

    /** @var coursereference_repository */
    private coursereference_repository $coursereferencerepository;

    /**
     * Constructs a new instance of the grid_view_service class and initializes the repositories.
     */
    public function __construct() {
        $this->skillrepository = new skill_repository();
        $this->subskillrepository = new subskill_repository();
        $this->userinputrepository = new userinput_repository();
        $this->leveldefinitionrepository = new leveldefinition_repository();
        $this->levelinstancerepository = new levelinstance_repository();
        $this->coursereferencerepository = new coursereference_repository();
    }

    /**
     * Pulls the data to render a competencygrid from the database and structures it for rendering.
     *
     * @param int $userid ID of the current user.
     * @param int $gridid The ID of the competency grid.
     * @return array The structured data for rendering.
     * Returns one arrayelement per skill, each containing a list of subskills and their leveldescriptions.
     */
    public function get_render_data(int $userid, int $gridid): array {
        [, $skills, $userinputs] = $this->load_grid_data($userid, $gridid);

        // Create a map of subskillid => levelniveau for quick lookup.
        // As we get the data by timemodified ASC this means for each subskill only the most recent data will be in $userselections.
        $userselections = [];
        foreach ($userinputs as $input) {
            $userselections[$input->subskillid] = $input->levelniveau;
        }

        $leveldefinitions = $this->leveldefinitionrepository->get_leveldefinitions_by_gridid($gridid);

        // Build table header. Only needed once, as every skill should have same amount + descr. of levels.
        $tableheader = [get_string('skill', 'mod_competencygrid')];
        foreach ($leveldefinitions as $leveldefinition) {
            $tableheader[] = $leveldefinition->title; // Use the 'title' field for the header.
        }

        // READ DATA FROM SETTINGS.
        $settingsinfo = [];
        $settingsinfo['defaulttextcolor']        = get_config('mod_competencygrid', 'defaulttextcolor');
        $settingsinfo['secondarytextcolor']      = get_config('mod_competencygrid', 'secondarytextcolor');
        $settingsinfo['previouslyselectedcolor'] = get_config('mod_competencygrid', 'previouslyselectedcolor');
        $settingsinfo['currentselectioncolor']   = get_config('mod_competencygrid', 'currentselectioncolor');

        $renderdata = [];
        foreach ($skills as $skillindex => $skill) {
            // Fetch subskills for the current skill.
            $subskills = $this->subskillrepository->get_subskills_by_skillid($skill->id);

            $indexedsubskills = [];
            foreach ($subskills as $subskill) {
                // Fetch level instances for the current subskill.
                $levelinstances = $this->levelinstancerepository->get_levelinstances_by_subskillid($subskill->id);

                $indexedleveldescriptions = [];
                foreach ($levelinstances as $levelinstance) {
                    // Check if this level was previously selected by the user. Somewhat obtuse because of linelenght limit.
                    $isset = isset($userselections[$subskill->id]);
                    $isselected = $isset && ( $userselections[$subskill->id] === $levelinstance->niveaunumeric );

                    $indexedleveldescriptions[] = [
                            'description' => $levelinstance->description,
                            'level_id' => $levelinstance->niveaunumeric,
                            'previouslyselected' => $isselected,
                    ];
                }

                $courses = $this->get_courses_for_subskillid($subskill->id);

                $indexedsubskills[] = [
                        'name' => $subskill->name,
                        'subskill_id' => $subskill->id, // Unique ID for the subskill.
                        'level_descriptions' => $indexedleveldescriptions, // Level descriptions for this subskill.
                        'courses' => $courses,
                        'visibility' => $subskill->visibility,
                ];
            }

            $renderdata[] = [
                    'skill_index' => $skillindex,
                    'qualification_title' => $skill->name,
                    'subskills' => $indexedsubskills,
                    'table_header' => $tableheader,
                    'settingsinfo' => $settingsinfo,
                    'visibility' => $skill->visibility,
            ];
        }
        return $renderdata;
    }

    /**
     * Returns annotated user inputs for a given grid
     * @param int $userid
     * @param int $gridid
     * @return array
     */
    public function get_current_user_inputs(int $userid, int $gridid): array {
        [$leveltitles, $skillrecords, $userinputrecords] = $this->load_grid_data($userid, $gridid);

        // As we get the data by timemodified ASC this means for each subskill only the most recent data will be in $userselections.
        $userselections = [];
        foreach ($userinputrecords as $input) {
            $userselections[$input->subskillid] = $input->levelniveau;
        }

        $skills = [];
        foreach ($skillrecords as $skill) {
            // 4. Fetch subskills for the current skill.
            $subskillrecords = $this->subskillrepository->get_subskills_by_skillid($skill->id);

            $subskills = [];
            foreach ($subskillrecords as $subskill) {
                $subskillid = $subskill->id;
                $subskills[] = [
                        'subskillid'   => $subskillid,
                        'subskillname' => $subskill->name,
                        'levelniveau'  => $userselections[$subskillid] ?? 0,
                ];
            }

            $skills[] = [
                    'skillid'   => $skill->id,
                    'skillname' => $skill->name,
                    'subskills' => $subskills,
            ];
        }

        return [
                'leveltitles'     => $leveltitles,
                'skills'           => $skills,
        ];
    }


    /**
     * Returns historic user inputs for a given grid
     *
     * @param int $userid
     * @param int $gridid
     * @return array
     */
    public function get_historic_user_inputs(int $userid, int $gridid): array {
        [$leveltitles, $skillrecords, $userinputrecords] = $this->load_grid_data($userid, $gridid);

        // Index user inputs: valuemap[subskillid][timemodified] = levelniveau .
        $valuemap = [];
        foreach ($userinputrecords as $rec) {
            $valuemap[$rec->subskillid][$rec->timemodified] = $rec->levelniveau;
        }

        $skills = [];
        foreach ($skillrecords as $skill) {
            // Fetch subskills for this skill.
            $subskillrecords = $this->subskillrepository->get_subskills_by_skillid($skill->id);

            // Collect all timestamps that belong to THIS skill only.
            // (i.e. any timestamp where at least one of its subskills was updated).
            $skilltimestamp = [];
            foreach ($subskillrecords as $subskill) {
                $sid = $subskill->id;
                if (isset($valuemap[$sid])) {
                    foreach (array_keys($valuemap[$sid]) as $ts) {
                        $skilltimestamp[$ts] = true;
                    }
                }
            }
            $skilltimestamp = array_keys($skilltimestamp);
            sort($skilltimestamp);

            // Build each subskills time series with LOCF (last observation carried forward) across $skilltimestamp.
            $subskills = [];
            foreach ($subskillrecords as $subskill) {
                $sid = $subskill->id;

                $timeseries = [];
                $lastknown  = 0; // 0 = no observation yet (leading gap).

                foreach ($skilltimestamp as $ts) {
                    if (isset($valuemap[$sid][$ts])) {
                        $lastknown = $valuemap[$sid][$ts];
                    }
                    $timeseries[$ts] = $lastknown;
                }

                $subskills[] = [
                        'subskillid'   => $sid,
                        'subskillname' => $subskill->name,
                        'timeseries'   => $timeseries,
                ];
            }

            $skills[] = [
                    'skillid'         => $skill->id,
                    'skillname'       => $skill->name,
                    'skilltimestamp'  => $skilltimestamp,
                    'subskills'       => $subskills,
            ];
        }

        return [
                'leveltitles' => $leveltitles,
                'skills'           => $skills,
        ];
    }

    /**
     * Retrieves data for and prepares (nested) inplace_editables for editing the grid.
     *
     * @param int $gridid
     * @return array
     */
    public function get_grid_edit_data(int $gridid): array {
        $gridedit = [];

        $leveldefinitions = $this->leveldefinitionrepository->get_leveldefinitions_by_gridid($gridid);

        foreach ($leveldefinitions as $leveldefinition) {
            $leveldefinitionentry = [];
            $editable = new competencygrid_inplace_editable_leveldefinition($leveldefinition);
            $leveldefinitionentry['editable'][] = $editable->export();
            $gridedit['leveldefinitions'][] = $leveldefinitionentry;
        }

        $skills = $this->skillrepository->get_skills_by_gridid($gridid);

        foreach ($skills as $skill) {

            $skillentry = [
                    'editable' => [],
                    'subskills' => [],
                    'visibility' => $skill->visibility,
            ];

            $skillentry['addsubskillurl'] = [
                    'url' => (new moodle_url('/mod/competencygrid/updategrid.php', [
                            'gridid'    => $gridid,
                            'skillid' => $skill->id,
                            'action'    => 'addsubskill',
                    ]))->out(false),
                    'sesskey' => sesskey(),
            ];

            $skillentry['setvisibilityurl'] = [
                    'url' => (new moodle_url('/mod/competencygrid/updategrid.php', [
                            'gridid'    => $gridid,
                            'skillid' => $skill->id,
                            'action'    => 'setvisibility',
                            'visibility' => (int) !$skill->visibility,
                    ]))->out(false),
                    'sesskey' => sesskey(),
            ];

            $skillentry['deleteurl'] = [
                    'url' => (new moodle_url('/mod/competencygrid/updategrid.php', [
                            'gridid'    => $gridid,
                            'skillid' => $skill->id,
                            'action'    => 'delete',
                    ]))->out(false),
                    'sesskey' => sesskey(),
            ];

            $editable = new competencygrid_inplace_editable_skill($skill);
            $skillentry['editable'][] = $editable->export();

            foreach ($skill->subskills() as $subskill) {

                $subskillentry = [
                        'editable' => [],
                        'levelinstances' => [],
                        'visibility' => $subskill->visibility,
                ];

                $subskillentry['setvisibilityurl'] = [
                        'url' => (new moodle_url('/mod/competencygrid/updategrid.php', [
                                'gridid'    => $gridid,
                                'subskillid' => $subskill->id,
                                'skillid' => $skill->id,
                                'action'    => 'setvisibility',
                                'visibility' => (int) !$subskill->visibility,
                        ]))->out(false),
                        'sesskey' => sesskey(),
                ];

                $subskillentry['deleteurl'] = [
                        'url' => (new moodle_url('/mod/competencygrid/updategrid.php', [
                                'gridid'    => $gridid,
                                'skillid' => $skill->id,
                                'subskillid' => $subskill->id,
                                'action'    => 'delete',
                        ]))->out(false),
                        'sesskey' => sesskey(),
                ];

                $editable = new competencygrid_inplace_editable_subskill($subskill);
                $subskillentry['editable'][] = $editable->export();

                foreach ($subskill->levelinstances() as $levelinstance) {
                    $editable = new competencygrid_inplace_editable_levelinstance($levelinstance);

                    $subskillentry['levelinstances'][] = [
                    'editable' => $editable->export(),
                    ];
                }
                $skillentry['subskills'][] = $subskillentry;
            }

            $gridedit['skills'][] = $skillentry;
        }

        $gridedit['addskillurl'] = [
                'url' => (new moodle_url('/mod/competencygrid/updategrid.php', [
                        'gridid'    => $gridid,
                        'action'    => 'addskill',
                ]))->out(false),
                'sesskey' => sesskey(),
        ];

        return $gridedit;
    }

    // PRIVATE HELPERS.

    /**
     * Load the userinputs and level definitions for a given grid, and prepare mapping array of niveaunumeric->title in leveltitles.
     * @return array{0: array<int,string>, 1: array, 2: array} [leveltitles,skills,userinputs]
     */
    private function load_grid_data(int $userid, int $gridid): array {
        $userinputs = $this->userinputrepository->get_userinputs_by_gridid($userid, $gridid);
        $leveldefinitions = $this->leveldefinitionrepository->get_leveldefinitions_by_gridid($gridid);
        $skills     = $this->skillrepository->get_skills_by_gridid($gridid);

        $leveltitles = [];
        foreach ($leveldefinitions as $ld) {
            $leveltitles[$ld->niveaunumeric] = $ld->title;
        }

        return [$leveltitles, $skills, $userinputs];
    }

    /**
     * Builds a lightweight courses array, that only contains the courses id, name and url
     * @param int $subskillid
     * @return array
     */
    private function get_courses_for_subskillid(int $subskillid): array {
        global $DB;
        $courseids = $this->coursereferencerepository->get_coursereferenceids_by_subskillid($subskillid);

        $courses = [];
        foreach ($courseids as $courseid) {

            $courses[] = [
                    'courseid'      => $courseid,
                    'fullname'    => $DB->get_record('course', ['id' => $courseid])->fullname,
                    'viewurl'     => new moodle_url('/course/view.php', ['id' => $courseid]),
            ];
        }
        return $courses;
    }

    /**
     * Loads userinputs from the Database and adds additional information (max, value, color, progress percentage, sufficiency).
     * Sufficiency is calculated upon subskill basis (level reached?) and then propagated to the skill (all subskills sufficient?)
     * and then finally, the grid is sufficient if all skills were sufficient.
     * @param int $userid
     * @param int $gridid
     * @return array
     */
    public function get_fit_grid_data(int $userid, int $gridid): array {
        global $DB;
        $renderdata = $this->get_current_user_inputs($userid, $gridid);
        $subskillservice = new subskill_service();
        $leveldefinitionservice = new leveldefinition_service();
        $userinputservice = new userinput_service();

        $numlevels = $leveldefinitionservice->get_level_count($gridid);
        $numsubskills = count($subskillservice->get_subskills_by_gridid($gridid));

        $value = $userinputservice->get_userinput_sum($userid, $gridid);
        $max = $numlevels * $numsubskills;
        $data['value'] = $value;
        $data['max'] = $max;
        $data['color'] = $this->calc_color($value, $max);
        $data['progress'] = $value / $max * 100;

        $data['skills'] = $renderdata['skills'];

        $sufficiencylevel = $DB->get_field('competencygrid', 'sufficiencylevel', ['id' => $gridid]);

        $gridsufficient = true;

        // Update reference, not clone: &.
        foreach ($data['skills'] as &$skill) {
            $skillmax = 0;
            $skillvalue = 0;

            $skillsufficient = true;

            foreach ($skill['subskills'] as &$subskill) {
                $subskillmax = $numlevels;
                $subskillvalue = $subskill['levelniveau'];

                $subskill['value'] = $subskillvalue;
                $subskill['max'] = $subskillmax;
                $subskill['progress'] = $subskillvalue / $subskillmax * 100;
                $subskill['color'] = $this->calc_color($subskillvalue, $subskillmax);
                $subskill['sufficient'] = $subskillvalue >= $sufficiencylevel;
                $courses = $this->get_courses_for_subskillid($subskill['subskillid']);
                $subskill['courses'] = $courses;
                if (count($courses) > 0 ) {
                    $subskill['hascourses'] = true;
                }

                // If it is already false: leave it at false, otherwise set to value of current subskill.
                // That way it only becomes true, if it is true FOR ALL subskills.
                $skillsufficient = $skillsufficient && $subskill['sufficient'];

                $skillmax += $numlevels;
                $skillvalue += $subskill['levelniveau'];

            }
            unset($subskill);
            // If it is already false: leave it at false, otherwise set to value of current skill.
            // That way it only becomes true, if it is true FOR ALL skills.
            $gridsufficient = (!$gridsufficient) ? false : $skillsufficient;
            $skill['value'] = $skillvalue;
            $skill['max'] = $skillmax;
            $skill['progress'] = $skillvalue / $skillmax * 100;
            $skill['color'] = $this->calc_color($skillvalue, $skillmax);
            $skill['sufficient'] = $skillsufficient;
        }
        unset($skill);

        $data['sufficient'] = $gridsufficient;

        return $data;
    }

    /**
     * Helper function to calculate an hsv color based on a value and a maximum.
     *
     * @param int $val The value for which to calculate the color.
     * @param int $max The maximum possible value.
     * @return string The calculated color in either hex or hsl format.
     */
    public function calc_color(int $val, int $max): string {
        if ($val === 0) {
            return '#cccccc';
        }
        $hue = round(($val - 1) / ($max - 1) * 170);
        return "hsla($hue, 50%, 60%,1)";
    }

    /**
     * Builds the course suggestions based upon the coursereferences and the userinputs where the user has less than the previously
     * specified sufficiencylevel.
     * @param int $userid
     * @param int $gridid
     * @return array
     */
    public function get_course_suggestions(int $userid, int $gridid): array {
        global $OUTPUT, $DB;
        $coursesuggestions = [];
        $userinputservice = new userinput_service();
        $userinputs = $userinputservice->get_current_userinputs($userid, $gridid);

        // Default to true only if there were userinputs since otherwise the following loop is not accessed and sufficient=true.
        $sufficient = count($userinputs) > 0;
        $sufficiencylevel = $DB->get_field('competencygrid', 'sufficiencylevel', ['id' => $gridid]);

        // Invariant for the whole call: build the favourites service once.
        $usercontext = context_user::instance($userid);
        $ufservice = service_factory::get_service_for_user_context($usercontext);

        foreach ($userinputs as $userinput) {
            if ($userinput->levelniveau >= $sufficiencylevel) {
                continue;
            }
            $sufficient = false;

            $courseids = $this->coursereferencerepository->get_coursereferenceids_by_subskillid($userinput->subskillid);

            foreach ($courseids as $courseid) {
                if (isset($coursesuggestions[$courseid])) {
                    continue; // Already added by another subskill.
                }

                $course = get_course($courseid);
                $context = context_course::instance($courseid);

                $courseimage = course_summary_exporter::get_course_image($course);
                if (!$courseimage) {
                    $courseimage = $OUTPUT->get_generated_image_for_id($courseid);
                }

                $category = core_course_category::get($course->category, IGNORE_MISSING);

                $isfavourite = $ufservice->favourite_exists(
                        'core_course',
                        'courses',
                        $courseid,
                        $context
                );

                $coursesuggestions[$courseid] = [
                        'courseid'           => $courseid,
                        'fullname'           => format_string($course->fullname, true, ['context' => $context]),
                        'viewurl'            => new moodle_url('/course/view.php', ['id' => $courseid]),
                        'showcoursecategory' => true,
                        'visible'            => (bool) $course->visible,
                        'courseimage'        => $courseimage,
                        'coursecategory'     => $category ? $category->get_formatted_name() : '',
                        'isfavourite'        => $isfavourite,
                ];
            }
        }

        return [
                'courses'    => array_values($coursesuggestions),
                'sufficient' => $sufficient,
        ];
    }

}
