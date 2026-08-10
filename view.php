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
use mod_competencygrid\local\service\userinput_service;

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

$userinputservice = new userinput_service();

// PROCESS USER INPUT/ FORM SUBMISSION.
if (data_submitted() && confirm_sesskey()) {

    // Extract parameters from request.
    $userinput = optional_param_array('userinput', [], PARAM_INT);
    $userid = $USER->id;
    $courseid = $course->id;
    $gridid = $moduleinstance->id;

    // Persist data with parameters.
    $userinputservice->save_userinput_array($userinput, $userid, $courseid, $gridid);

    // Redirect to avoid resubmission.
    redirect($PAGE->url, get_string('datapersisted', 'mod_competencygrid'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$gridviewservice = new \mod_competencygrid\local\service\grid_view_service();

// READ DATA FROM DB.
$renderdata = $gridviewservice->get_render_data($USER->id, $moduleinstance->id);

// Redirect to Editing Page when start editing.
if (has_capability('mod/competencygrid:edit', $modulecontext) && $PAGE->user_is_editing()) {
    redirect(new moodle_url('/mod/competencygrid/gridedit.php', ['id' => $cm->id]));
}

// PAGE OUTPUT STARTS HERE.
echo $OUTPUT->header();

// User cannot edit / is not currently editing.
if ((!has_capability('mod/competencygrid:edit', $modulecontext)) || (!$PAGE->user_is_editing())) {
    foreach ($renderdata as $data) {
        if ($data['visibility']) {
            $section = new collapsable_section(
                    $data['skillname'],
                    $OUTPUT->render_from_template('mod_competencygrid/grid', $data),
            // Could also add open: true,.
            );
            echo $OUTPUT->render($section);
        }
    }
}

echo $OUTPUT->footer();
