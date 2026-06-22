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

use mod_competencygrid\local\entity\leveldefinition;

/**
 * Class leveldefinition_repository
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leveldefinition_repository {
    /**
     * Retrieve all leveldefinitions by grid ID, sorted Ascending by their niveaunumeric.
     *
     * @param int $gridid Grid ID.
     * @return leveldefinition[]|null Array of leveldefinition entities or null if none found.
     */
    public function get_leveldefinitions_by_gridid(int $gridid): ?array {
        global $DB;
        $record = $DB->get_records('competencygrid_leveldefinitions', ['gridid' => $gridid], 'niveaunumeric ASC');
        return leveldefinition::from_records($record);
    }
}
