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

use mod_competencygrid\local\entity\subskill;

/**
 * Class subskill_repository
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subskill_repository {
    /**
     * Retrieve all skill records.
     *
     * @return subskill[]|null Array of Skill entities or null if none found.
     */
    public function get_subskills_all(): ?array {
        global $DB;
        $records = $DB->get_records('competencygrid_subskills');
        return subskill::from_records($records);
    }

    /**
     * Retrieve skill records by grid ID.
     *
     * @param int $gridid Grid ID.
     * @return subskill[]|null Array of Skill entities or null if none found.
     */
    public function get_subskills_by_gridid(int $gridid): ?array {
        global $DB;
        $skillids = $DB->get_fieldset('competencygrid_skills', 'id' ,['gridid' => $gridid]);
        [$insql, $params] = $DB->get_in_or_equal($skillids);
        $records = $DB->get_records_select('competencygrid_subskills', "skillid $insql", $params);
        return subskill::from_records($records);
    }

    /**
     * Retrieve skill records by Skill ID.
     *
     * @param int $skillid Skill ID.
     * @return subskill[]|null Array of Skill entities or null if none found.
     */
    public function get_subskills_by_skillid(int $skillid): ?array {
        global $DB;
        $record = $DB->get_records('competencygrid_subskills', ['skillid' => $skillid]);
        return subskill::from_records($record);
    }

    /**
     * Retrieve a skill record by its ID.
     *
     * @param int $skillid Skill ID.
     * @return Skill Skill entity.
     */
    public function get_subskill_by_id(int $subskillid): subskill {
        global $DB;
        $record = $DB->get_record('competencygrid_subskills', ['id' => $subskillid]);
        return subskill::from_record($record);
    }
}
