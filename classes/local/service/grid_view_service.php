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
use mod_competencygrid\local\entity\coursereference;
use mod_competencygrid\local\repository\coursereference_repository;
use mod_competencygrid\local\repository\leveldefinition_repository;
use mod_competencygrid\local\repository\levelinstance_repository;
use mod_competencygrid\local\repository\skill_repository;
use mod_competencygrid\local\repository\subskill_repository;
use mod_competencygrid\local\repository\userinput_repository;
use moodle_url;

/**
 * Class grid_view_service. Aggregates and prepares data from different repositories for rendering.
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grid_view_service {

    private skill_repository $skill_repository;
    private subskill_repository $subskill_repository;
    private userinput_repository $userinput_repository;
    private leveldefinition_repository $leveldefinition_repository;
    private levelinstance_repository $levelinstance_repository;
    private coursereference_repository $coursereference_repository;

    public function __construct() {
        $this->skill_repository = new skill_repository();
        $this->subskill_repository = new subskill_repository();
        $this->userinput_repository = new userinput_repository();
        $this->leveldefinition_repository = new leveldefinition_repository();
        $this->levelinstance_repository = new levelinstance_repository();
        $this->coursereference_repository = new coursereference_repository();
    }

    public function get_render_data(int $userid, int $gridid) {
        global $DB;

        // Fetch user's previous selections.
        $userinputs = $this->userinput_repository->get_userinputs_by_gridid($userid, $gridid);

        // Create a map of subskillid => levelniveau for quick lookup.
        // As we get the data by timemodified ASC this means for each subskill only the most recent data will be in $userselections.
        $userselections = [];
        foreach ($userinputs as $input) {
            $userselections[$input->subskillid] = $input->levelniveau;
        }

        $skills = $this->skill_repository->get_skills_by_gridid($gridid);
        $leveldefinitions = $this->leveldefinition_repository->get_leveldefinitions_by_gridid($gridid);

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
            $subskills = $this->subskill_repository->get_subskills_by_skillid($skill->id);

            $indexedsubskills = [];
            foreach ($subskills as $subskill) {
                // Fetch level instances for the current subskill.
                $levelinstances = $this->levelinstance_repository->get_levelinstances_by_subskillid($subskill->id);


                $indexedleveldescriptions = [];
                foreach ($levelinstances as $levelinstance) {
                    // Check if this level was previously selected by the user. Somewhat obtuse because of linelenght limit.
                    $isset = isset($userselections[$subskill->id]);
                    $isselected = $isset && ( $userselections[$subskill->id] == $levelinstance->niveaunumeric );

                    $indexedleveldescriptions[] = [
                            'description' => $levelinstance->description,
                            'level_id' => $levelinstance->niveaunumeric,
                            'previouslyselected' => $isselected,
                    ];
                }

                $courseids = $this->coursereference_repository->get_coursereferenceids_by_subskillid($subskill->id);

                $courses = [];
                foreach ($courseids as $courseid) {
                    $courses[] = [
                            'courseid'      => $courseid,
                            'coursename'    => $DB->get_record('course', ['id' => $courseid])->fullname,
                            'courseurl'     => new moodle_url('/course/view.php', ['id' => $courseid]),
                    ];
                }

                $indexedsubskills[] = [
                        'name' => $subskill->name,
                        'subskill_id' => $subskill->id, // Unique ID for the subskill.
                        'level_descriptions' => $indexedleveldescriptions, // Level descriptions for this subskill.
                        'courses' => $courses,
                ];
            }

            $renderdata[] = [
                    'skill_index' => $skillindex,
                    'qualification_title' => $skill->name,
                    'subskills' => $indexedsubskills,
                    'table_header' => $tableheader,
                    'settingsinfo' => $settingsinfo,
            ];
        }
        return $renderdata;
    }

}