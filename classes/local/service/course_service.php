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

namespace mod_competencygrid\local\service;

use mod_competencygrid\local\repository\coursereference_repository;
use moodle_url;

/**
 * Course Service Class
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_service {

    /**
     * Builds a lightweight courses array, that only contains the courses id, name and url
     * @param int $subskillid
     * @return array
     */
    public static function get_courses_for_subskillid(int $subskillid): array {
        global $DB;
        $courseids = (new coursereference_repository())->get_coursereferenceids_by_subskillid($subskillid);

        $courses = [];
        foreach ($courseids as $courseid) {

            $courses[] = [
                    'courseid'      => $courseid,
                    'fullname'    => $DB->get_record('course', ['id' => $courseid])->fullname,
                    'viewurl'     => new moodle_url('/course/view.php', ['id' => $courseid]),
            ];
        }
        return $courses;
    }
}
