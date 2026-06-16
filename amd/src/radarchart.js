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
        init: function(skillId) {
            var chartData = getChartData(skillId);
            if (!chartData) {
                return;
            }

            // Wait for the DOM to be fully ready.
            var canvasId = 'radar-' + skillId;
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
                const {flat, transparent} = getSkillColors(data.values, data.maxLevel);
                var ctx = document.getElementById(id).getContext('2d');
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: data.timemodified,
                            data: data.values,
                            backgroundColor: transparent,
                            borderColor: flat,
                            borderWidth: 2,
                            pointBackgroundColor: data.colorsflat,
                            pointBorderColor: data.colorsflat,
                            pointRadius: 4,
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

    /**
     * Fetch chart JSON from a hidden script tag.
     * @param {number} skillId The skill id to resolve.
     * @returns {object|null}
     */
    function getChartData(skillId) {
        var script = document.getElementById('chart-data-' + skillId);
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

/**
 * Calculate the flat and transparent color for the radar chart area.
 * @param {array} values Array of values to which we want to assign colors
 * @param {number} maxLevel Maximum level attainable in the data
 */
function getSkillColors(values, maxLevel) {
    if (!values.length || maxLevel <= 1) {
        return {flat: 'hsla(0, 0%, 60%, 1)', transparent: 'hsla(0, 0%, 60%, 0.5)' };
    }

    const avg = values.reduce((sum, v) => sum + v, 0) / values.length;

    // Normalize to hue 0-170
    const raw = (avg - 1) / (maxLevel - 1) * 170;
    const hue = Math.round(Math.max(0, Math.min(170, raw)));

    return {
        flat:        `hsla(${hue}, 50%, 60%, 1)`,
        transparent: `hsla(${hue}, 50%, 60%, 0.5)`,
    };
}
