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

use mod_competencygrid\local\entity\userinput;
use mod_competencygrid\local\repository\subskill_repository;
use mod_competencygrid\local\repository\userinput_repository;

/**
 * Class userinput_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userinput_service {
    /** @var userinput_repository */
    private userinput_repository $userinputrepository;
    /** @var subskill_repository */
    private subskill_repository $subskillrepository;

    /**
     * Constructs a new instance of the userinput_service class and initializes the repository.
     */
    public function __construct() {
        $this->userinputrepository = new userinput_repository();
        $this->subskillrepository = new subskill_repository();
    }

    /**
     * Saves an array of user inputs.
     *
     * @param array $userinput The array of user inputs to save.
     * @param int $userid The ID of the user for which to save inputs.
     * @param int $courseid The ID of the course for which to save inputs.
     * @param int $gridid The ID of the competency grid for which to save inputs.
     */
    public function save_userinput_array(array $userinput, int $userid, int $courseid, int $gridid) {
        global $DB;

        $skillservice = new skill_service();
        $subskillservice = new subskill_service();

        $transaction = $DB->start_delegated_transaction();

        foreach ($userinput as $subskillid => $levelniveau) {
            // Validate and sanitize inputs.
            $subskillid = (int) $subskillid;
            $levelniveau = (int) $levelniveau;
            // Get the skillid from the subskill.
            $subskill = $subskillservice->get_subskill($subskillid);
            $skillid = $skillservice->get_skill($subskill)->id;
            // Persist data trough repository.
            $userinputobject = new userinput();
            $userinputobject->userid = $userid;
            $userinputobject->courseid = $courseid;
            $userinputobject->gridid = $gridid;
            $userinputobject->skillid = $skillid;
            $userinputobject->subskillid = $subskillid;
            $userinputobject->levelniveau = $levelniveau;
            $this->save_userinput($userinputobject);
        }
        $transaction->allow_commit();
    }

    /**
     * Saves a single user input.
     *
     * @param userinput $userinput The user input to save.
     */
    public function save_userinput(userinput $userinput) {
        $this->userinputrepository->write_userinput($userinput);
    }

    /**
     * Returns the most recent user inputs for a given user and grid. Maximally one user input per subskill.
     * @param int $userid
     * @param int $gridid
     * @return userinput[]
     */
    public function get_current_userinputs(int $userid, int $gridid): ?array {
        $userinputs = $this->userinputrepository->get_userinputs_by_gridid($userid, $gridid);
        $userselections = [];

        // Get all skills for this grid, as we need to know their ids.
        $subskills = $this->subskillrepository->get_subskills_by_gridid($gridid);
        // Pad userinputs to have 0 when no input was performed.
        foreach ($subskills as $subskill) {
            $input = new userinput();
            $input->levelniveau = 0;
            $input->userid = $userid;
            $input->subskillid = $subskill->id;
            $userselections[$subskill->id] = $input;
        }

        // As we get the data by timemodified ASC this means for each subskill only the most recent data will be in $userselections.
        foreach ($userinputs as $input) {
            $userselections[$input->subskillid] = $input;
        }
        return array_values($userselections);
    }

    /**
     * Returns the sum of all levelniveaus for a given user and grid.
     * @param int $userid
     * @param int $gridid
     * @return int
     */
    public function get_userinput_sum(int $userid, int $gridid): int {
        $userinputs = $this->get_current_userinputs($userid, $gridid);
        $sum = 0;
        foreach ($userinputs as $userinput) {
            $sum += $userinput->levelniveau;
        }
        return $sum;
    }
}
