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
 * Prints an instance of mod_competencygrid.
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\local\collapsable_section;


require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$c = optional_param('c', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('competencygrid', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('competencygrid', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('competencygrid', ['id' => $c], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('competencygrid', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);
require_capability('mod/competencygrid:view', $modulecontext);

// Log the view event.
$event = \mod_competencygrid\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('competencygrid', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/competencygrid/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// PROCESS USER INPUT/ FORM SUBMISSION.
if (data_submitted() && confirm_sesskey()) {
    $userinput = optional_param_array('userinput', [], PARAM_INT);
    $userid = $USER->id;
    $courseid = $course->id;
    $gridid = $moduleinstance->id;

    $now = time();
    $startoftoday = usergetmidnight($now);

    $transaction = $DB->start_delegated_transaction();

    foreach ($userinput as $subskillid => $levelniveau) {
        // Validate and sanitize inputs.
        $subskillid = (int) $subskillid;
        $levelniveau = (int) $levelniveau;

        // Get the skillid from the subskill.
        $subskill = $DB->get_record('competencygrid_subskills', ['id' => $subskillid]);
        $skillid = $subskill ? $subskill->skillid : null;

        // Check if the record already exists.
        $existing = $DB->get_records('competencygrid_userinput', [
            'userid' => $userid,
            'courseid' => $courseid,
            'gridid' => $gridid,
            'subskillid' => $subskillid,
        ], 'timemodified DESC'); // Newest entry first.

        $overwriting = false;  // Default.
        $recordtooverwrite = null;

        // Check if user already did this input today, otherwise overwrite it.
        // TODO MDL-00001 expose the granularity as a setting.
        if (!empty($existing)) {
            $recordtooverwrite = reset($existing);
            // If last modification date of an input was today we want to overwrite, otherwise we add it as new record.
            // $overwriting = ($recordtooverwrite->timemodified >= $startoftoday);
            // If we don't want to overwrite we should check if there are already 9 entries for this.
        }

        if ($overwriting) {
            // Update the existing record.
            $recordtooverwrite->levelniveau = $levelniveau;
            $recordtooverwrite->timemodified = $now;
            $DB->update_record('competencygrid_userinput', $recordtooverwrite);
        } else {
            // Insert a new record.
            $newrecord = new stdClass();
            $newrecord->userid = $userid;
            $newrecord->courseid = $courseid;
            $newrecord->gridid = $gridid;
            $newrecord->skillid = $skillid;
            $newrecord->subskillid = $subskillid;
            $newrecord->levelniveau = $levelniveau;
            $newrecord->timecreated = $now;
            $newrecord->timemodified = $now;
            $DB->insert_record('competencygrid_userinput', $newrecord);
        }
    }
    $transaction->allow_commit();

    // Redirect to avoid resubmission.
    redirect($PAGE->url, get_string('datapersisted', 'mod_competencygrid'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// READ DATA FROM DB.
$renderdata = competencygrid_get_render_data_from_db($moduleinstance->id);


// PAGE OUTPUT STARTS HERE.
echo $OUTPUT->header();

if (has_capability('mod/competencygrid:edit', $modulecontext) && $PAGE->user_is_editing()) {
    //foreach ($renderdata as $data) {
    //    $section = new collapsable_section(
    //            $data['qualification_title'],
    //            $OUTPUT->render_from_template('mod_competencygrid/grid_edit', $data),
    //            open: true,
    //    );
    //    echo $OUTPUT->render($section);
    //}
} else {
    foreach ($renderdata as $data) {
        $section = new collapsable_section(
                $data['qualification_title'],
                $OUTPUT->render_from_template('mod_competencygrid/grid', $data),
        // Could also add open: true,.
        );
        echo $OUTPUT->render($section);
    }
}

echo $OUTPUT->footer();
