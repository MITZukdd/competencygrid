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
class cleanup_old_userinput extends scheduled_task {
    /**
     * Gets the display name for the cleanup task
     *
     * @return lang_string|string
     */
    public function get_name(): lang_string|string {
        $cutoff = (int) get_config('mod_competencygrid', 'cleanup_cutoff');
        if ($cutoff < 1) {
            $cutoff = 1; // Fallback for unallowed values.
        }
        return get_string('cleanup_old_userinput', 'mod_competencygrid', $cutoff);
    }

    /**
     * Functionality that is executed in order to clean up the competencygrid_userinput table once in a while.
     * Gathers all userinput older than X years and subsequently deletes them
     *
     * @return void
     */
    public function execute() {
        global $DB;

        // Records older than X months.
        $months = (int) get_config('mod_competencygrid', 'cleanup_cutoff');
        if ($months < 1) {
            $months = 1; // Fallback for unallowed values.
        }
        $cutoff = strtotime("{$months} months");

        // Delete in batches to avoid huge transactions / long table locks.
        $batchsize = 1000;
        do {
            $ids = $DB->get_fieldset_select(
                'competencygrid_userinput',
                'id',
                'timecreated < ?',
                [$cutoff],
                0,
                $batchsize
            );

            if (!empty($ids)) {
                [$insql, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
                $DB->delete_records_select('competencygrid_userinput', "id $insql", $params);
            }
        } while (count($ids) === $batchsize);
    }
}
