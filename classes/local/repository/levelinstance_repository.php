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

use mod_competencygrid\local\entity\levelinstance;

/**
 * Class leveldefinition_repository
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelinstance_repository {
    /**
     * Retrieve all levelinstances by grid ID.
     *
     * @param int $gridid Grid ID.
     * @return levelinstance[]|null Array of levelinstance entities or null if none found.
     */
    public function get_levelinstances_by_subskillid(int $subskillid): ?array {
        global $DB;
        $records = $DB->get_records('competencygrid_levelinstances', ['subskillid' => $subskillid], 'niveaunumeric ASC');
        return levelinstance::from_records($records);
    }

    public function insert_levelinstance(levelinstance $levelinstance): int {
        global $DB;
        // Levelinstance already persisted
        if (isset($levelinstance->id)) {
            return -1;
        }
        return $DB->insert_record('competencygrid_levelinstances', $levelinstance);
    }
}
