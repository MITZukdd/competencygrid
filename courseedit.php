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
 * Fit for the OSCE page for mod_competencygrid.
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('competencygrid', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('competencygrid', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    throw new moodle_exception('missingparameter');
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);
require_capability('mod/competencygrid:view', $modulecontext);
require_capability('mod/competencygrid:edit', $modulecontext);

// Log the view event.
$event = \mod_competencygrid\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('competencygrid', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/competencygrid/courseedit.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name . ' - ' . get_string('tab:courseedit', 'mod_competencygrid')));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);


$gridviewservice = new \mod_competencygrid\local\service\grid_view_service();
$renderdata = $gridviewservice->get_render_data($USER->id, $moduleinstance->id);


$actionlink = new moodle_url('/mod/competencygrid/courseedit.php', ['id' => $cm->id]);

$courseeditform = new \mod_competencygrid\form\competencygrid_coursedropdownform($actionlink, [
        'id'     => $id,
        'renderdata'   => $renderdata,
]);

if ($courseeditform->is_cancelled()) {
    redirect(new moodle_url('/mod/competencygrid/courseedit.php', ['id' => $id]));
}

if ($formdata = $courseeditform->get_selections()) {
    foreach ($formdata as $formindex => $formvaluearray) {
        // Some courses can have multiple courses associated.

        // Get course ids that are currently associated with the subskill.
        // Courseids : current array of association.
        // FormvalueArray : from frontend send data.

        $courseids = $DB->get_fieldset_select(
            'competencygrid_coursereferences',
            'courseid',
            'subskillid = ?',
            [$formindex]
        );

        // Data from DB is String, but data from frontend is int.
        $courseids = array_map('intval', $courseids);


        sort($courseids);
        sort($formvaluearray);

        // Do nothing if nothing changed.
        if ($courseids === $formvaluearray) {
            continue;
        } else {
            // Courses that should be removed from the list.
            $removecourseids = array_diff($courseids, $formvaluearray);
            foreach ($removecourseids as $courseid) {
                $data = [
                        'subskillid' => $formindex,
                        'courseid' => $courseid,
                ];
                $DB->delete_records('competencygrid_coursereferences', $data);
            }

            // Courses that are added by the user.
            $newcourseids = array_diff($formvaluearray, $courseids);
            foreach ($newcourseids as $courseid) {
                $data = [
                        'subskillid' => $formindex,
                        'courseid' => $courseid,
                ];
                $DB->insert_record('competencygrid_coursereferences', $data);
            }
        }
    }

    redirect(
        new moodle_url('/mod/competencygrid/courseedit.php', ['id' => $id]),
        get_string('datapersisted', 'mod_competencygrid'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$PAGE->activityheader->set_description(format_text(get_string('intro:coursereferences', 'mod_competencygrid'), FORMAT_HTML));

echo $OUTPUT->header();
echo $courseeditform->render();
echo $OUTPUT->footer();
