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
 * TODO describe module linehistorychart
 *
 * @module     mod_competencygrid/historychart
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/chartjs',
    'mod_competencygrid/luxon',
    'mod_competencygrid/chartjs-adapter-luxon'
    ],
    function(Chart) {
    return {
        init: function(skillId) {
            var chartData = getChartData(skillId);
            if (!chartData)
            {
                return;
            }

            // Wait for the DOM to be fully ready.
            var canvasId = 'skill-' + 'historic-line-' + skillId;
            var canvas = document.getElementById(canvasId);

            if (!canvas) {
                // Fallback: wait for DOMContentLoaded if canvas isn't found yet.
                document.addEventListener('DOMContentLoaded', function() {
                    buildChart(canvasId, chartData);
                });
            } else {
                buildChart(canvasId, chartData);
            }

            /**
             * Pass the data to the chart, and adjust color and axis
             * @param {string} id Id that is used in the html template file
             * @param {object} data chart data
             */
            function buildChart(id, data) {

                // const sumData = data.datasets[0].data.map((_, i) =>
                //     data.datasets.reduce((sum, ds) => sum + (ds.data[i] ?? 0), 0)
                // );
                //
                // data.datasets.push({
                //     label: 'Sum',
                //     data: sumData,
                //     borderWidth: 5,
                //     borderDash: [6, 4],
                //     fill: false,
                //     tension: 0.3,
                // });

                var ctx = document.getElementById(id).getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                stacked: false,
                                max: data.maxLevel,
                            },
                            x: {
                                type: 'time',
                                // time: {
                                //     unit: 'week'
                                // }
                            }
                        },
                        tension: 0.3
                    }
                });
            }
        }
    };

    /**
     * Fetch chart JSON from a hidden script tag.
     * @param {number} skillId The skill id to resolve.
     * @returns {object|null}
     */
    function getChartData(skillId) {
        var script = document.getElementById('chart-data-' + skillId + '-historic');
        if (!script || !script.textContent) {
            return null;
        }

        try {
            return JSON.parse(script.textContent);
        } catch (e) {
            return null;
        }
    }
});
