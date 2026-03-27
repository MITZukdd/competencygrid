<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function competencygrid_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_competencygrid into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_competencygrid_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function competencygrid_add_instance($moduleinstance, $mform = null) {
    global $DB;
    global $USER;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('competencygrid', $moduleinstance);

    $file = $mform->get_file_content('templatefile');

    if ($file) {
        persist_uploaded_csv_file($file, $id);
    }

    return $id;
}

/**
 * Updates an instance of the mod_competencygrid in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_competencygrid_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function competencygrid_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('competencygrid', $moduleinstance);
}

/**
 * Removes an instance of the mod_competencygrid from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function competencygrid_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('competencygrid', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('competencygrid', ['id' => $id]);

    return true;
}

/**
 * Persists an uploaded CSV file for a competency grid.
 *
 * @param string $filecontent The content of the uploaded CSV file.
 * @param int $competencygridid The ID of the competency grid.
 * @return bool True if successful, false on failure.
 */
function persist_uploaded_csv_file($filecontent, $competencygridid) {
    global $DB;
    $record = new stdClass();

    $lines = explode("\n", $filecontent);
    $header = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        // Parse the CSV line.
        $data = str_getcsv($line);
        // Handle the header row.
        if (!$header) {
            $header = $data;
            continue;
        }

        // Map the data to the columns.
        $skillname = array_shift($data); // Column 1: Skill name.
        $subskillname = array_shift($data); // Column 2: Subskill name.

        $levels = [];
        for ($i = 0; $i < count($data); $i++) {
            $leveldescription = $data[$i];
            $levenumeric = $i + 1; // Start numeric levels from 1.
            $leveltext = $header[$i + 2];
            $levels[] = [
            'description' => $leveldescription,
            'niveaunumeric' => $levenumeric,
            'niveautext' => $leveltext,
            ];
        }

        // Insert or retrieve the skill.
        $skill = $DB->get_record('competencygrid_skills', ['name' => $skillname, 'gridid' => $competencygridid]);
        if (!$skill) {
            $skill = new stdClass();
            $skill->name = $skillname;
            $skill->gridid = $competencygridid;
            $skill->id = $DB->insert_record('competencygrid_skills', $skill);
        }

        // Insert or retrieve the subskill.
        $subskill = $DB->get_record('competencygrid_subskills', ['name' => $subskillname, 'skillid' => $skill->id]);
        if (!$subskill) {
            $subskill = new stdClass();
            $subskill->name = $subskillname;
            $subskill->skillid = $skill->id;
            $subskill->id = $DB->insert_record('competencygrid_subskills', $subskill);
        }

        // Insert the levels for the subskill.
        foreach ($levels as $level) {
            $leveldefinition = $DB->get_record('competencygrid_leveldefinitions', [
            'niveaunumeric' => $level['niveaunumeric'],
            'gridid' => $competencygridid,
            ]);

            if (!$leveldefinition) {
                $leveldefinition = new stdClass();
                $leveldefinition->title = $level['niveautext'];
                $leveldefinition->niveaunumeric = $level['niveaunumeric'];
                $leveldefinition->niveautext = $level['niveautext'];
                $leveldefinition->gridid = $competencygridid;
                $leveldefinition->id = $DB->insert_record('competencygrid_leveldefinitions', $leveldefinition);
            }
        }

        foreach ($levels as $level) {
            $levelinstance = $DB->get_record('competencygrid_levelinstances', [
            'description' => $level['description'],
            'niveaunumeric' => $level['niveaunumeric'],
            'subskillid' => $subskill->id,
            ]);

            if (!$levelinstance) {
                $levelinstance = new stdClass();
                $levelinstance->description = $level['description'];
                $levelinstance->niveaunumeric = $level['niveaunumeric'];
                $levelinstance->subskillid = $subskill->id;
                $DB->insert_record('competencygrid_levelinstances', $levelinstance);
            }
        }
    }
    return true;
}

/**
 * Pulls the data to render a competencygrid from the database and structures it for rendering.
 *
 * @param int $competencygridid The ID of the competency grid.
 * @return array The structured data for rendering.
 */
function get_render_data_from_db($competencygridid) {
    global $DB;

    $skills = $DB->get_records('competencygrid_skills', ['gridid' => $competencygridid]);
    $leveldefinitions = $DB->get_records('competencygrid_leveldefinitions', ['gridid' => $competencygridid], 'niveaunumeric ASC');

    $tableheader = ['Skill'];
    foreach ($leveldefinitions as $leveldefinition) {
        $tableheader[] = $leveldefinition->title; // Use the 'title' field for the header.
    }

    $renderdata = [];
    foreach ($skills as $skillindex => $skill) {
        // Fetch subskills for the current skill.
        $subskills = $DB->get_records('competencygrid_subskills', ['skillid' => $skill->id]);

        $indexedsubskills = [];
        foreach ($subskills as $subskill) {
            // Fetch level instances for the current subskill.
            $levelinstances = $DB->get_records('competencygrid_levelinstances', ['subskillid' => $subskill->id], 'niveaunumeric ASC');

            $indexedleveldescriptions = [];
            foreach ($levelinstances as $levelinstance) {
                $indexedleveldescriptions[] = [
                'description' => $levelinstance->description,
                'level_id' => $levelinstance->niveaunumeric,
                ];
            }

            $indexedsubskills[] = [
            'name' => $subskill->name,
            'subskill_id' => $subskill->id, // Unique ID for the subskill.
            'level_descriptions' => $indexedleveldescriptions, // Level descriptions for this subskill.
            ];
        }

        $renderdata[] = [
        'skill_index' => $skillindex,
        'qualification_title' => $skill->name,
        'subskills' => $indexedsubskills,
        'table_header' => $tableheader,
        ];
    }
    return $renderdata;
}
