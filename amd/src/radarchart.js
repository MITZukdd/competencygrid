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

/**
 * TODO describe module radarchart
 *
 * @module     mod_competencygrid/radarchart
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/chartjs'], function(Chart) {
    return {
        init: function(chartData) {
            // Wait for the DOM to be fully ready.
            var canvasId = 'radar-' + chartData.skillId;
            var canvas = document.getElementById(canvasId);

            if (!canvas) {
                // Fallback: wait for DOMContentLoaded if canvas isn't found yet.
                document.addEventListener('DOMContentLoaded', function() {
                    buildChart(canvasId, chartData);
                });
            } else {
                buildChart(canvasId, chartData);
            }

            function buildChart(id, data) {
                var ctx = document.getElementById(id).getContext('2d');
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: '', // TODO: assign date from timecreated or timemodified for tracking over time
                            data: data.values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: data.maxLevel,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        // Use level titles if available.
                                        if (data.levelTitles && data.levelTitles[value - 1]) {
                                            return data.levelTitles[value - 1];
                                        }
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    };
});
