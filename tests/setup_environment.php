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
 * Static class which for environment setup
 *
 * @package     mod_competencygrid
 * @category    test
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_competencygrid;

trait setup_environment {

    /**
     * Sets up the test database using the provided csv file
     * @param string $csv
     * @return \stdClass
     * @throws \coding_exception
     */
    protected function setup_db(string $csv = 'mockcompetencygrid.csv', $options = null): \stdClass {
        global $CFG;
        $this->resetAfterTest();

        $generator = self::getDataGenerator();
        $course = $generator->create_course();

        $options['course'] = $course->id;
        $options['templatecsvpath'] = $CFG->dirroot . '/mod/competencygrid/tests/fixtures/' . $csv;

        $instance = $generator->create_module('competencygrid', $options);

        return (object) [
                'course'   => $course,
                'instance' => $instance,
                'cm'       => get_coursemodule_from_instance('competencygrid', $instance->id),
                'context'  => \context_module::instance($instance->cmid),
        ];
    }

    protected function load_user_inputs_to_db(\stdClass $env, \stdClass $user, string $csv = 'mockuserinputs.csv'): void {
        global $CFG;
        $content = file_get_contents(
                $CFG->dirroot . '/mod/competencygrid/tests/fixtures/' . $csv
        );
        $content = str_replace(
                ['USERID', 'COURSEID', 'GRIDID'],
                [$user->id, $env->course->id, $env->instance->id],
                $content
        );

        self::dataset_from_string($content, 'csv', 'competencygrid_userinputs')->to_database();
    }
}