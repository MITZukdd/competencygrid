<?php
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
 * Contains Generator for mod_competencygrid
 *
 * @package     mod_competencygrid
 * @category    test
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/competencygrid/lib.php');

class mod_competencygrid_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null): stdClass {
        global $CFG;
        $record = (array) $record;

        $defaults = [
                'name' => 'Test',
                'intro' => 'Test intro',
                'introformat' => FORMAT_HTML,
                'sufficiencylevel' => 3,
                'showfit' => 1,
                'niveau0option' => 2,
                'niveau0customtext' => '',
                'fitintro' => '',
                'analysisintro' => '',
                'defaultchart' => 'radar',
                'defaulthistoricchart' => 'historic-bar',
        ];

        foreach ($defaults as $name => $value) {
            if (!isset($record[$name])) {
                $record[$name] = $value;
            }
        }

        $instance = parent::create_instance((object) $record, (array) $options);

        // Use supplied if given, otherwise use valid one.
        $csvpath = $record['templatecsvpath']
                ?? $CFG->dirroot . '/mod/competencygrid/tests/fixtures/mockcompetencygrid.csv';
        unset($record['templatecsvpath']);

        $instance = parent::create_instance((object) $record, (array) $options);

        competencygrid_persist_uploaded_csv_file(
                file_get_contents($csvpath),
                $instance->id,
                $record['niveau0option'],
                $record['niveau0customtext']
        );

        return $instance;
    }
}
