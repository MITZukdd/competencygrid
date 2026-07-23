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

use mod_competencygrid\event\course_module_viewed;
use mod_competencygrid\local\service\grid_view_service;

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

// Log the view event.
$event = course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('competencygrid', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/competencygrid/fit.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name . ' - ' . get_string('tab:fit', 'mod_competencygrid')));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);


$gridviewservice = new grid_view_service();
$data = $gridviewservice->get_fit_grid_data($USER->id, $moduleinstance->id);

$coursesuggestions = $gridviewservice->get_course_suggestions($USER->id, $moduleinstance->id);


$fitintro = $DB->get_field('competencygrid', 'fitintro', ['id' => $moduleinstance->id]);
$fitformat = $DB->get_field('competencygrid', 'fitintroformat', ['id' => $moduleinstance->id]);
$PAGE->activityheader->set_description(format_text($fitintro, $fitformat));

// PAGE OUTPUT STARTS HERE.
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('mod_competencygrid/course_suggestions', $coursesuggestions);

echo $OUTPUT->render_from_template('mod_competencygrid/fit', $data);

echo $OUTPUT->footer();
