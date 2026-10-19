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

use core\output\inplace_editable;
use mod_competencygrid\output\competencygrid_inplace_editable_leveldefinition;
use mod_competencygrid\output\competencygrid_inplace_editable_levelinstance;
use mod_competencygrid\output\competencygrid_inplace_editable_skill;
use mod_competencygrid\output\competencygrid_inplace_editable_subskill;

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
        case FEATURE_BACKUP_MOODLE2:
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
 * @param object $data An object from the form.
 * @param mod_competencygrid_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function competencygrid_add_instance($data, $mform = null) {
    global $DB;
    $data->timecreated = time();

    $fit = $data->fitintro;
    if (isset($fit) && is_array($fit)) {
        $data->fitintro = $fit['text'];
        $data->fitintroformat = $fit['format'];
    }
    $analysis = $data->analysisintro;
    if (isset($analysis) && is_array($analysis)) {
        $data->analysisintro = $analysis['text'];
        $data->analysisintroformat = $analysis['format'];
    }

    $id = $DB->insert_record('competencygrid', $data);

    $niveau0option = $data->niveau0option ?? 0;
    $niveau0customtext = $data->niveau0customtext ?? '';

    if ($mform) {
        $file = $mform->get_file_content('templatefile');
    }
    if (isset($file) && $file) {
        competencygrid_persist_uploaded_csv_file($file, $id, $niveau0option, $niveau0customtext);
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

    $fit = $moduleinstance->fitintro;
    if (isset($fit) && is_array($fit)) {
        $moduleinstance->fitintro = $fit['text'];
        $moduleinstance->fitintroformat = $fit['format'];
    }
    $analysis = $moduleinstance->analysisintro;
    if (isset($analysis) && is_array($analysis)) {
        $moduleinstance->analysisintro = $analysis['text'];
        $moduleinstance->analysisintroformat = $analysis['format'];
    }

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

    // Get all skills for this grid.
    $skills = $DB->get_records('competencygrid_skills', ['gridid' => $id]);

    if ($skills) {
        // Get all skill IDs.
        $skillids = array_keys($skills);

        // Get all subskills for these skills.
        [$insql, $params] = $DB->get_in_or_equal($skillids);
        $subskills = $DB->get_records_select('competencygrid_subskills', "skillid $insql", $params);

        if ($subskills) {
            // Get all subskill IDs.
            $subskillids = array_keys($subskills);

            // Delete all levelinstances for these subskills.
            [$insql, $params] = $DB->get_in_or_equal($subskillids);
            $DB->delete_records_select('competencygrid_levelinstances', "subskillid $insql", $params);
            // Delete course references for deleted subskills.
            $DB->delete_records_select('competencygrid_coursereferences', "subskillid $insql", $params);
        }

        // Delete all subskills for these skills.
        [$insql, $params] = $DB->get_in_or_equal($skillids);
        $DB->delete_records_select('competencygrid_subskills', "skillid $insql", $params);
    }

    // Delete all skills for this grid.
    $DB->delete_records('competencygrid_skills', ['gridid' => $id]);

    // Delete all level definitions for this grid.
    $DB->delete_records('competencygrid_leveldefinitions', ['gridid' => $id]);

    // Delete all user inputs for this grid.
    $DB->delete_records('competencygrid_userinput', ['gridid' => $id]);

    // Delete the grid itself.
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
function competencygrid_persist_uploaded_csv_file($filecontent, $competencygridid, $niveau0option, $niveau0customtext) {
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
        $skillname = clean_param(array_shift($data), PARAM_TEXT); // Column 1: Skill name.
        $subskillname = clean_param(array_shift($data), PARAM_TEXT); // Column 2: Subskill name.

        $levels = [];

        // Support for adding level 0 per settings when adding a new grid.
        if ($niveau0option != 0) {
            $levels[] = [
            // Add custom text if user chooses the option, otherwise field stays empty.
            'description' => $niveau0option == 1 ? clean_param($niveau0customtext, PARAM_TEXT) : '',
            'niveaunumeric' => 0,
            'niveautext' => '', // No header, does not need localization.
            ];
        }
        $countlevels = count($levels);
        for ($i = 0, $imax = count($data); $i < $imax; $i++) {
            $leveldescription = clean_param($data[$i], PARAM_TEXT);
            // Start numeric levels from i.
            // which is 0 if level 0 was not added manually (and should therefore be included in the uploaded file).
            // and 1 if it was added per the settings.
            $levenumeric = $i + $countlevels;
            $leveltext = clean_param($header[$i + 2], PARAM_TEXT);
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
            $skill->visibility = true;
            $skill->id = $DB->insert_record('competencygrid_skills', $skill);
        }

        // Insert or retrieve the subskill.
        $subskill = $DB->get_record('competencygrid_subskills', ['name' => $subskillname, 'skillid' => $skill->id]);
        if (!$subskill) {
            $subskill = new stdClass();
            $subskill->name = $subskillname;
            $subskill->skillid = $skill->id;
            $subskill->visibility = true;
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
 * Extend the settings navigation with a link to the fit.php page.
 *
 * @param settings_navigation $settings The settings navigation object.
 * @param navigation_node $competencygridnode The competencygrid node in the settings navigation.
 */
function competencygrid_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $competencygridnode) {
    global $DB;

    // These don't need capabilitychecks, because they should be visible to everyone.

    $url = new moodle_url('/mod/competencygrid/analysis.php', ['id' => $settingsnav->get_page()->cm->id]);
    $analysisnode = navigation_node::create(
        get_string('tab:analysis', 'mod_competencygrid'),
        $url,
        navigation_node::TYPE_SETTING
    );
    $analysisnode->set_show_in_secondary_navigation(true);
    $competencygridnode->add_node($analysisnode);

    $showfit = $DB->get_field('competencygrid', 'showfit', ['id' => $settingsnav->get_page()->cm->instance]);
    if ($showfit) {
        $url = new moodle_url('/mod/competencygrid/fit.php', ['id' => $settingsnav->get_page()->cm->id]);
        $fitnode = navigation_node::create(
            $DB->get_field('competencygrid', 'fittab', ['id' => $settingsnav->get_page()->cm->instance]),
            $url,
            navigation_node::TYPE_SETTING
        );
        $fitnode->set_show_in_secondary_navigation(true);
        $competencygridnode->add_node($fitnode);
    }

    $context = context_module::instance($settingsnav->get_page()->cm->id);
    if (has_capability('mod/competencygrid:edit', $context )) {
        $url = new moodle_url('/mod/competencygrid/courseedit.php', ['id' => $settingsnav->get_page()->cm->id]);
        $courseeditnode = navigation_node::create(
                get_string('tab:courseedit', 'mod_competencygrid'),
                $url,
                navigation_node::TYPE_SETTING
        );
        $courseeditnode->set_show_in_secondary_navigation(true);
        $competencygridnode->add_node($courseeditnode);
    }

}


/**
 * Handles inplace editable updates for competencygrid items.
 *
 * @param string $itemtype The type of item being edited.
 * @param int $itemid The ID of the item being edited.
 * @param string $newvalue The new value for the item.
 * @return inplace_editable The updated inplace_editable object.
 * @throws moodle_exception If the itemtype is unknown.
 */
function mod_competencygrid_inplace_editable($itemtype, $itemid, $newvalue) {

    if ($itemtype === 'skill') {
        return competencygrid_inplace_editable_skill::update($itemid, $newvalue);
    }
    if ($itemtype === 'subskill') {
        return competencygrid_inplace_editable_subskill::update($itemid, $newvalue);
    }
    if ($itemtype === 'levelinstance') {
        return competencygrid_inplace_editable_levelinstance::update($itemid, $newvalue);
    }
    if ($itemtype === 'leveldefinition') {
        return competencygrid_inplace_editable_leveldefinition::update($itemid, $newvalue);
    }
    // If itemtype is unknown, throw an error.
    throw new \core\exception\coding_exception('Unknown Itemtype: ' . $itemtype);
}
