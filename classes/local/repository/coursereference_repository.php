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

use mod_competencygrid\local\entity\coursereference;

/**
 * Class coursereference_repository
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursereference_repository {
    /**
     * Retrieve all coursereferences by grid ID.
     *
     * @param int $gridid Grid ID.
     * @return coursereference[]|null Array of coursereference entities or null if none found.
     */
    public function get_coursereferenceids_by_subskillid(int $subskillid): ?array {
        global $DB;
        return $DB->get_fieldset_select(
            'competencygrid_coursereferences',
            'courseid',
            'subskillid = ?',
            [$subskillid]
        );
    }
}
