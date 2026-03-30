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

/**
 * Provides all the settings and steps to perform one complete backup of the activity
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_competencygrid_activity_structure_step extends backup_activity_structure_step {
    /**
     * Backup structure
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        $attributes = ['id'];
        $finalelements = ['name', 'intro', 'introformat', 'timemodified', 'timecreated'];
        $root = new backup_nested_element('competencygrid', $attributes, $finalelements);

        // Skills.
        $skills = new backup_nested_element('skills');
        $skill = new backup_nested_element('skill', ['id'], ['name', 'gridid']);

        // Subskills.
        $subskills = new backup_nested_element('subskills');
        $subskill = new backup_nested_element('subskill', ['id'], ['name', 'skillid']);

        // Level definitions.
        $leveldefinitions = new backup_nested_element('leveldefinitions');
        $leveldefinition = new backup_nested_element('leveldefinition', ['id'], ['title', 'niveaunumeric', 'gridid']);

        // Level instances.
        $levelinstances = new backup_nested_element('levelinstances');
        $levelinstance = new backup_nested_element('levelinstance', ['id'], ['description', 'niveaunumeric', 'subskillid']);

        // User inputs (only if userinfo).
        $userinputs = new backup_nested_element('userinputs');
        $userinput = new backup_nested_element(
            'userinput',
            ['id'],
            ['userid', 'courseid', 'gridid', 'skillid', 'subskillid', 'levelniveau', 'timecreated', 'timemodified']
        );

        // Build the tree.
        $root->add_child($skills);
        $skills->add_child($skill);
        $skill->add_child($subskills);
        $subskills->add_child($subskill);
        $subskill->add_child($levelinstances);
        $levelinstances->add_child($levelinstance);

        $root->add_child($leveldefinitions);
        $leveldefinitions->add_child($leveldefinition);

        if ($userinfo) {
            $root->add_child($userinputs);
            $userinputs->add_child($userinput);
        }

        // Define sources.
        $root->set_source_table('competencygrid', ['id' => backup::VAR_ACTIVITYID]);

        $skill->set_source_table('competencygrid_skills', ['gridid' => backup::VAR_PARENTID]);
        $subskill->set_source_table('competencygrid_subskills', ['skillid' => backup::VAR_PARENTID]);
        $levelinstance->set_source_table('competencygrid_levelinstances', ['subskillid' => backup::VAR_PARENTID]);
        $leveldefinition->set_source_table('competencygrid_leveldefinitions', ['gridid' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $userinput->set_source_table('competencygrid_userinput', ['gridid' => backup::VAR_PARENTID]);
        }

        // Annotate IDs.
        if ($userinfo) {
            $userinput->annotate_ids('user', 'userid');
            $userinput->annotate_ids('course', 'courseid');
        }

        // Define file annotations.
        $root->annotate_files('mod_competencygrid', 'intro', null);

        // Return the root element (competencygrid), wrapped into standard activity structure.
        return $this->prepare_activity_structure($root);
    }
}
