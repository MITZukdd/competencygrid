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
use core_external\external_api;

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
 * @param object $moduleinstance An object from the form.
 * @param mod_competencygrid_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function competencygrid_add_instance($moduleinstance, $mform = null) {
    global $DB;
    global $USER;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('competencygrid', $moduleinstance);

    $niveau0option = $mform->get_data()->niveau0option ?? 0;
    $niveau0customtext = $mform->get_data()->niveau0customtext ?? '';

    $file = $mform->get_file_content('templatefile');

    if ($file) {
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
        $skillname = array_shift($data); // Column 1: Skill name.
        $subskillname = array_shift($data); // Column 2: Subskill name.

        $levels = [];

        // Support for adding level 0 per settings when adding a new grid.
        if ($niveau0option != 0) {
            $levels[] = [
            // Add custom text if user chooses the option, otherwise field stays empty.
            'description' => $niveau0option == 1 ? $niveau0customtext : '',
            'niveaunumeric' => 0,
            'niveautext' => '', // No header, does not need localization.
            ];
        }

        for ($i = count($levels); $i < count($data); $i++) {
            $leveldescription = $data[$i];
            // Start numeric levels from i.
            // which is 0 if level 0 was not added manually (and should therefore be included in the uploaded file).
            // and 1 if it was added per the settings.
            $levenumeric = $i;
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
 * Returns one arrayelement per skill, each containing a list of subskills and their leveldescriptions.
 */
function competencygrid_get_render_data_from_db($competencygridid, $userid = null) {
    global $DB, $USER;

    if ($userid === null) {
        $userid = $USER->id;
    }

    // Fetch user's previous selections.
    $userinputs = $DB->get_records('competencygrid_userinput', [
        'userid' => $userid,
        'gridid' => $competencygridid,
    ], 'subskillid ASC, timemodified ASC');

    // Create a map of subskillid => levelniveau for quick lookup.
    // As we get the data by timemodified ASC this means for each subskill only the most recent data will be in $userselections.
    $userselections = [];
    foreach ($userinputs as $input) {
        $userselections[$input->subskillid] = $input->levelniveau;
    }

    $skills = $DB->get_records('competencygrid_skills', ['gridid' => $competencygridid]);
    $leveldefinitions = $DB->get_records('competencygrid_leveldefinitions', ['gridid' => $competencygridid], 'niveaunumeric ASC');

    $tableheader = ['Skill'];
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
        $subskills = $DB->get_records('competencygrid_subskills', ['skillid' => $skill->id]);

        $indexedsubskills = [];
        foreach ($subskills as $subskill) {
            // Fetch level instances for the current subskill.
            $levelinstances = $DB->get_records(
                'competencygrid_levelinstances',
                ['subskillid' => $subskill->id],
                'niveaunumeric ASC'
            );

            $indexedleveldescriptions = [];
            foreach ($levelinstances as $levelinstance) {
                // Check if this level was selected by the user. Somewhat obtuse because of linelenght limit.
                $isset = isset($userselections[$subskill->id]);
                $isselected = $isset && $userselections[$subskill->id] == $levelinstance->niveaunumeric;

                $indexedleveldescriptions[] = [
                'description' => $levelinstance->description,
                'level_id' => $levelinstance->niveaunumeric,
                'previouslyselected' => $isselected,
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
        'settingsinfo' => $settingsinfo,
        ];
    }
    return $renderdata;
}


/**
 * Returns annotated user inputs for a given grid, structured as:
 * [
 *   'leveldefinitions' => [ ['niveaunumeric' => 1, 'title' => 'Beginner'], ... ],
 *   'skills' => [
 *       [
 *           'skillid'   => 1,
 *           'skillname' => 'Communication',
 *           'subskills' => [
 *               [
 *                   'subskillid'   => 10,
 *                   'subskillname' => 'Listening',
 *                   'levelniveau'  => 3,  // 0 if no selection
 *               ],
 *               ...
 *           ],
 *       ],
 *       ...
 *   ],
 * ]
 *
 * @param int $userid
 * @param int $gridid
 * @return array
 */
function competencygrid_get_annotated_user_inputs(int $userid, int $gridid): array {
    global $DB;

    // 1. Fetch level definitions for this grid (sorted ascending).
    $leveldefinitionrecords = $DB->get_records(
        'competencygrid_leveldefinitions',
        ['gridid' => $gridid],
        'niveaunumeric ASC'
    );

    $leveldefinitions = [];
    foreach ($leveldefinitionrecords as $ld) {
        $leveldefinitions[] = [
            'niveaunumeric' => (int) $ld->niveaunumeric,
            'title'         => $ld->title,
        ];
    }

    // 2. Fetch all user inputs for this grid and index by subskillid.
    $userinputrecords = $DB->get_records('competencygrid_userinput', [
        'userid' => $userid,
        'gridid' => $gridid,
    ], 'subskillid ASC, timemodified ASC');

    // As we get the data by timemodified ASC this means for each subskill only the most recent data will be in $userselections.
    $userselections = [];
    foreach ($userinputrecords as $input) {
        $userselections[(int) $input->subskillid] = (int) $input->levelniveau;
    }

    // 3. Fetch all skills for this grid.
    $skillrecords = $DB->get_records('competencygrid_skills', ['gridid' => $gridid]);

    $skills = [];
    foreach ($skillrecords as $skill) {
        // 4. Fetch subskills for the current skill.
        $subskillrecords = $DB->get_records('competencygrid_subskills', ['skillid' => $skill->id]);

        $subskills = [];
        foreach ($subskillrecords as $subskill) {
            $subskillid = (int) $subskill->id;
            $subskills[] = [
                'subskillid'   => $subskillid,
                'subskillname' => $subskill->name,
                'levelniveau'  => $userselections[$subskillid] ?? 0,
            ];
        }

        $skills[] = [
            'skillid'   => (int) $skill->id,
            'skillname' => $skill->name,
            'subskills' => $subskills,
        ];
    }

    return [
        'leveldefinitions' => $leveldefinitions,
        'skills'           => $skills,
    ];
}

/**
 * Extend the settings navigation with a link to the fit.php page.
 *
 * @param settings_navigation $settings The settings navigation object.
 * @param navigation_node $competencygridnode The competencygrid node in the settings navigation.
 */
function competencygrid_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $competencygridnode) {

    // These don't need capabilitychecks, because they should be visible to everyone.

    $url = new moodle_url('/mod/competencygrid/analysis.php', ['id' => $settingsnav->get_page()->cm->id]);
    $analysisnode = navigation_node::create(
        get_string('tab:analysis', 'mod_competencygrid'),
        $url,
        navigation_node::TYPE_SETTING
    );
    $analysisnode->set_show_in_secondary_navigation(true);
    $competencygridnode->add_node($analysisnode);

    $url = new moodle_url('/mod/competencygrid/fit.php', ['id' => $settingsnav->get_page()->cm->id]);
    $fitnode = navigation_node::create(
        get_string('tab:fit', 'mod_competencygrid'),
        $url,
        navigation_node::TYPE_SETTING
    );
    $fitnode->set_show_in_secondary_navigation(true);
    $competencygridnode->add_node($fitnode);
}


/**
 * Handles inplace editable updates for competencygrid items.
 *
 * @param string $itemtype The type of item being edited.
 * @param int $itemid The ID of the item being edited.
 * @param string $newvalue The new value for the item.
 * @return \core\output\inplace_editable The updated inplace_editable object.
 * @throws moodle_exception If the itemtype is unknown.
 */
function mod_competencygrid_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB;

    if ($itemtype === 'mytextfield') {
        // Load the record.
        $record = $DB->get_record('competencygrid', ['id' => $itemid], '*', MUST_EXIST);

        // Must call validate_context for either system, or course or course module context.
        // This will both check access and set current context.
        \core_external\external_api::validate_context(context_system::instance());

        // Check permission of the user to update this item.
        require_capability('mod/competencygrid:edit', context_system::instance());

        // Clean input and update the record.
        $newvalue = clean_param($newvalue, PARAM_NOTAGS);
        $record->name = $newvalue;
        $DB->update_record('competencygrid', $record);

        // Return updated inplace_editable object.
        return new \core\output\inplace_editable(
            'mod_competencygrid',
            'mytextfield',
            $record->id,
            true,
            format_string($record->name),
            $record->name
        );
    }

    // If itemtype is unknown, throw an error.
    throw new moodle_exception('error');
}
