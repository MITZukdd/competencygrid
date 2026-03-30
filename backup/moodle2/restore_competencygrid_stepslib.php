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
 * Structure step to restore one Competency Grid activity
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_competencygrid_activity_structure_step extends restore_activity_structure_step {
    /**
     * Structure step to restore one competencygrid activity
     *
     * @return array
     */
    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');
        $paths[] = new restore_path_element('competencygrid', '/activity/competencygrid');
        $paths[] = new restore_path_element('competencygrid_skill', '/activity/competencygrid/skills/skill');
        $paths[] = new restore_path_element('competencygrid_subskill', '/activity/competencygrid/skills/skill/subskills/subskill');

        // Seperate this into 3 statements so that the codechecker stops complaining about the line lenght.
        $elementname = 'competencygrid_levelinstance';
        $elementpath = '/activity/competencygrid/skills/skill/subskills/subskill/levelinstances/levelinstance';
        $paths[] = new restore_path_element($elementname, $elementpath);

        $elementname = 'competencygrid_leveldefinition';
        $elementpath = '/activity/competencygrid/leveldefinitions/leveldefinition';
        $paths[] = new restore_path_element($elementname, $elementpath);

        if ($userinfo) {
            $paths[] = new restore_path_element('competencygrid_userinput', '/activity/competencygrid/userinputs/userinput');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a competencygrid restore
     *
     * @param array $data
     * @return void
     */
    protected function process_competencygrid($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        unset($data->id);
        $data->course = $this->get_courseid();

        // Insert the competencygrid record.
        $newitemid = $DB->insert_record('competencygrid', $data);
        $this->set_mapping('competencygrid', $oldid, $newitemid);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a competencygrid skill restore
     *
     * @param array $data
     * @return void
     */
    protected function process_competencygrid_skill($data) {
        global $DB;
        $data = (object)$data;

        $oldid = $data->id;
        unset($data->id);
        $data->gridid = $this->get_new_parentid('competencygrid');

        $newitemid = $DB->insert_record('competencygrid_skills', $data);
        $this->set_mapping('competencygrid_skill', $oldid, $newitemid);
    }

    /**
     * Process a competencygrid subskill restore
     *
     * @param array $data
     * @return void
     */
    protected function process_competencygrid_subskill($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        unset($data->id);
        $data->skillid = $this->get_new_parentid('competencygrid_skill');

        $newitemid = $DB->insert_record('competencygrid_subskills', $data);
        $this->set_mapping('competencygrid_subskill', $oldid, $newitemid);
    }

    /**
     * Process a competencygrid level instance restore
     *
     * @param array $data
     * @return void
     */
    protected function process_competencygrid_levelinstance($data) {
        global $DB;

        $data = (object)$data;
        unset($data->id);
        $data->subskillid = $this->get_new_parentid('competencygrid_subskill');

        $DB->insert_record('competencygrid_levelinstances', $data);
    }

    /**
     * Process a competencygrid level definition restore
     *
     * @param array $data
     * @return void
     */
    protected function process_competencygrid_leveldefinition($data) {
        global $DB;

        $data = (object)$data;
        unset($data->id);
        $data->gridid = $this->get_new_parentid('competencygrid');

        $DB->insert_record('competencygrid_leveldefinitions', $data);
    }

    /**
     * Process a competencygrid user input restore
     *
     * @param array $data
     * @return void
     */
    protected function process_competencygrid_userinput($data) {
        global $DB;

        $data = (object)$data;
        unset($data->id);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->courseid = $this->get_courseid();
        $data->gridid = $this->get_new_parentid('competencygrid');
        $data->skillid = $this->get_mappingid('competencygrid_skill', $data->skillid);
        $data->subskillid = $this->get_mappingid('competencygrid_subskill', $data->subskillid);

        $DB->insert_record('competencygrid_userinput', $data);
    }

    /**
     * Actions to be executed after the restore is completed
     */
    protected function after_execute() {
        // Add competencygrid related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_competencygrid', 'intro', null);
    }
}
