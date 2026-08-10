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

namespace mod_competencygrid\task;

use core\task\scheduled_task;
use lang_string;
use mod_competencygrid\local\repository\userinput_repository;

/**
 * The Cleanup task for abandoned userinput data
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_userinput extends scheduled_task {

    /**
     * Gets the display name for the cleanup task
     * @return lang_string|string
     */
    public function get_name(): lang_string|string {
        return get_string('cleanup_userinput', 'mod_competencygrid');
    }

    /**
     * Functionality that is executed in order to clean up the competencygrid_userinput table once in a while.
     * Gathers all ids from table user where deleted=1.
     * Then deletes all userinputs from the competencygrid_userinput table of the corresponding ids.
     * @return void
     */
    public function execute() {
        global $DB;
        $deletedusers = $DB->get_fieldset('user', 'id', ['deleted' => 1]);
        $userinputrepo = new userinput_repository();
        foreach ($deletedusers as $userid) {
            $userinputrepo->delete_userinputs_by_userid($userid);
        }
    }
}
