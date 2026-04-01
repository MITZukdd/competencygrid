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

$PAGE->set_url('/mod/competencygrid/analysis.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name . ' - ' . get_string('tab:analysis', 'mod_competencygrid')));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$renderdata = competencygrid_get_render_data_from_db($moduleinstance->id);

// Get the level definitions to determine the maximum level.
$leveldefinitions = $DB->get_records('competencygrid_leveldefinitions', ['gridid' => $moduleinstance->id], 'niveaunumeric ASC');
$maxlevel = count($leveldefinitions);

// PAGE OUTPUT STARTS HERE.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('tab:analysis', 'mod_competencygrid'), 2);

foreach ($renderdata as $skillblock) {
    // Prepare data for the chart.
    $labels = [];
    $values = [];
    foreach ($skillblock['subskills'] as $subskill) {
        $labels[] = $subskill['name'];
        // Find the selected level for this subskill.
        $selectedlevel = 0;
        foreach ($subskill['level_descriptions'] as $leveldesc) {
            if (!empty($leveldesc['previouslyselected'])) {
                $selectedlevel = $leveldesc['level_id'];
                break;
            }
        }
        $values[] = $selectedlevel;
    }

    // Calculate colors for each bar based on the level (gradient from red to green).
    $colors = [];
    foreach ($values as $value) {
        if ($value == 0) {
            $color = '#cccccc'; // Gray for no selection.
        } else {
            // Gradient from red (hue 0) to green (hue 120) in HSL.
            $hue = ($value - 1) / ($maxlevel - 1) * 90;
            $color = "hsl({$hue}, 50%, 50%)";
        }
        $colors[] = $color;
    }

    // I know this is cursed. TODO: better way to have graph respect maxvalue.?

    // $values[] = $maxlevel; // Add a dummy value to force the Y-axis maximum.
    // $labels[] = ''; // Add an empty label for the dummy value.
    // $colors[] = 'rgba(0,0,0,0)';



    // Create the bar chart.

    $series = new \core\chart_series('Selected Level', $values);
    $series->set_colors($colors);

    $chart = new \core\chart_bar();
    $chart->add_series($series);
    $chart->set_labels($labels);
    $chart->set_title($skillblock['qualification_title']);

    // Set Y-axis to have a maximum equal to the number of levels.
    $chart->get_yaxis(1, true)->set_labels(array_merge([''], array_slice($skillblock['table_header'], 1)));
    $yaxis = $chart->get_yaxis(1, true);
    $yaxis->set_max($maxlevel);
    $yaxis->set_min(0);
    $yaxis->set_stepsize(1);
    $chart->set_yaxis($yaxis);

    $chart->set_legend_options(['display' => false]);


    echo $OUTPUT->heading($skillblock['qualification_title'], 3);
    echo $OUTPUT->render($chart);
}

$PAGE->requires->js_amd_inline("
    require([], function() {
        var maxLevel = {$maxlevel};
        var patched = {};

        setTimeout(function() {
            Object.keys(Chart.instances).forEach(function(key) {
                var chart = Chart.instances[key];
                if (chart && chart.config.type === 'bar') {
                    // Access the underlying scale object directly.
                    var yScale = chart.scales.y;
                    console.log('Before - max:', yScale.max, 'options max:', yScale.options.max);

                    // Try setting it on the config level instead.
                    chart.config.options.scales.y.max = maxLevel;
                    chart.config.options.scales.y.min = 0;
                    chart.config.options.scales.y.ticks =
                        chart.config.options.scales.y.ticks || {};
                    chart.config.options.scales.y.ticks.stepSize = 1;

                    chart.update('none');

                    console.log('After - max:', chart.scales.y.max, 'options max:', chart.scales.y.options.max);
                }
            });
        }, 3000);
    });
");

echo $OUTPUT->footer();
