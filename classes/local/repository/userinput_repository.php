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

namespace mod_competencygrid\local\repository;

use mod_competencygrid\local\entity\userinput;
use stdClass;

/**
 * Class userinput_repository
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userinput_repository {
    /**
     * Write user input to the database.
     * @param userinput $userinput
     * @return void
     */
    public function write_userinput(userinput $userinput): void {
        global $DB;
        $now = time();
        $newrecord = new stdClass();
        $newrecord->userid = $userinput->userid;
        $newrecord->courseid = $userinput->courseid;
        $newrecord->gridid = $userinput->gridid;
        $newrecord->skillid = $userinput->skillid;
        $newrecord->subskillid = $userinput->subskillid;
        $newrecord->levelniveau = $userinput->levelniveau;
        $newrecord->timecreated = $now;
        $newrecord->timemodified = $now;
        $DB->insert_record('competencygrid_userinput', $newrecord);
    }

    /**
     * Retrieve all userinputs by a grid ID.
     *
     * @param int $userid User ID.
     * @param int $gridid Grid ID.
     * @return userinput[]|null Array of Userinput entities or null if none found.
     */
    public function get_userinputs_by_gridid(int $userid, int $gridid): ?array {
        global $DB;
        $records = $DB->get_records('competencygrid_userinput', [
                'userid' => $userid,
                'gridid' => $gridid,
        ], 'subskillid ASC, timemodified ASC');
        return userinput::from_records($records);
    }

    /**
     * Retrieve all userinputs by a skill ID.
     *
     * @param int $skillid Grid ID.
     * @return userinput[]|null Array of Userinput entities or null if none found.
     */
    public function get_userinputs_by_skillid(int $userid, int $skillid): ?array {
        global $DB;
        $records = $DB->get_records('competencygrid_userinput', [
                'userid' => $userid,
            'skillid' => $skillid,
        ], 'subskillid ASC, timemodified ASC');
        return userinput::from_records($records);
    }

    /**
     * Delete user inputs by user ID.
     *
     * @param int $userid User ID.
     * @return void
     */
    public function delete_userinputs_by_userid(int $userid): void {
        global $DB;
        $DB->delete_records('competencygrid_userinput', ['userid' => $userid]);
    }

    /**
     * Delete user inputs by grid ID.
     *
     * @param int $gridid Grid ID.
     * @return void
     */
    public function delete_userinputs_by_gridid(int $gridid): void {
        global $DB;
        $DB->delete_records('competencygrid_userinput', ['gridid' => $gridid]);
    }

    /**
     * Delete user inputs by user ID and grid ID.
     *
     * @param int $userid User ID.
     * @param int $gridid Grid ID.
     * @return void
     */
    public function delete_userinputs_by_userid_gridid(int $userid, int $gridid): void {
        global $DB;
        $DB->delete_records('competencygrid_userinput', ['userid' => $userid, 'gridid' => $gridid]);
    }
}
