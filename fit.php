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

// Log the view event.
$event = \mod_competencygrid\event\course_module_viewed::create([
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

// READ DATA FROM DATABASE
$userdata = competencygrid_get_annotated_user_inputs($USER->id, $moduleinstance->id);

$leveltitles = [];
foreach ($userdata['leveldefinitions'] as $ld) {
    $leveltitles[$ld['niveaunumeric']] = $ld['title'];
}

$maxlevel = count($userdata['leveldefinitions']);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('tab:fit', 'mod_competencygrid'), 2);

// Controller logic
$tabsdata = [
        ['id' => 'radar', 'label' => 'Radar Chart', 'active' => true],
        ['id' => 'polar', 'label' => 'Polar Chart', 'active' => false],
        ['id' => 'bar', 'label' => 'Bar Chart', 'active' => false],
];

$skills = [];

foreach ($userdata['skills'] as $index => $skillblock) {
    $labels = [];
    $values = [];

    foreach ($skillblock['subskills'] as $subskill) {
        $labels[] = $subskill['subskillname'];
        $values[] = $subskill['levelniveau'];
    }

    $colorsflat = [];
    $colorstransparent = [];
    foreach ($values as $value) {
        if ($value == 0) {
            $colorflat = '#cccccc'; // Gray for no selection.
            $colortransparent = '#cccccc80';
        } else {
            // Gradient from red (hue 0) to green (hue 90) in HSL.
            $hue = round(($value - 1) / ($maxlevel - 1) * 170, 0);
            $colorflat = "hsla({$hue}, 50%, 60%,1)";
            $colortransparent = "hsla({$hue}, 50%, 60%,0.5)";
        }
        $colorsflat[] = $colorflat;
        $colorstransparent[] = $colortransparent;
    }

    $chartdata = [
        'skillId'      => $skillblock['skillid'],
        'labels'       => $labels,
        'datasetLabel' => $skillblock['skillname'],
        'values'       => $values,
        'colorsflat'   => $colorsflat,
        'colorstransparent' => $colorstransparent,
        'maxLevel'     => $maxlevel,
        'levelTitles'  => array_values($leveltitles),
    ];

    $skills[] = [
        'skillid'   => $skillblock['skillid'],
        'skilltitle' => $skillblock['skillname'],
        'chartdatajson' => json_encode($chartdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ];

    $PAGE->requires->js_call_amd(
        'mod_competencygrid/radarchart',
        'init',
        [$skillblock['skillid']]
    );

    $PAGE->requires->js_call_amd(
        'mod_competencygrid/polarchart',
        'init',
        [$skillblock['skillid']]
    );
    $PAGE->requires->js_call_amd(
        'mod_competencygrid/barchart',
        'init',
        [$skillblock['skillid']]
    );
}

// Render a canvas for this skill.
echo $OUTPUT->render_from_template('mod_competencygrid/analysis', [
    'tabs'      => $tabsdata,
    'skills'    => $skills,
]);


echo $OUTPUT->footer();
