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

use mod_competencygrid\local\entity\skill;
use mod_competencygrid\local\entity\subskill;

/**
 * Class skill_repository
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skill_repository {
    /**
     * Retrieve skill records by grid ID.
     *
     * @param int $gridid Grid ID.
     * @return Skill[]|null Array of Skill entities or null if none found.
     */
    public function get_skills_by_gridid(int $gridid): ?array {
        global $DB;
        $record = $DB->get_records('competencygrid_skills', ['gridid' => $gridid]);
        return Skill::from_records($record);
    }

    /**
     * Retrieve a skill record by its ID.
     *
     * @param int $skillid Skill ID.
     * @return Skill|null Skill entity.
     */
    public function get_skill_by_id(int $skillid): ?Skill {
        global $DB;
        $record = $DB->get_record('competencygrid_skills', ['id' => $skillid]);
        if (!$record) {
            return null;
        }
        return Skill::from_record($record);
    }

    /**
     * Retrieve a skill from a subskillid
     *
     * @param subskill $subskill Skill ID.
     * @return Skill Skill entity.
     */
    public function get_skill_by_subskill(subskill $subskill): skill {
        global $DB;
        $skillid = $DB->get_field('competencygrid_subskills', 'skillid', ['id' => $subskill->id]);
        $record = $DB->get_record('competencygrid_skills', ['id' => $skillid]);
        return Skill::from_record($record);
    }

    /**
     * Inserts a new skill into the database. returns the id of the newly created entry.
     * @param skill $skill
     * @return int
     */
    public function insert_skill(skill $skill): int {
        global $DB;
        // Skill already persisted.
        if (isset($skill->id)) {
            return -1;
        }
        return $DB->insert_record('competencygrid_skills', $skill);
    }

    /**
     * Update a skill object in the db
     *
     * @param skill $skill
     * @return bool
     */
    public function update_skill(skill $skill): bool {
        global $DB;
        if (!isset($skill->id)) {
            return false;
        }
        return $DB->update_record('competencygrid_skills', $skill);
    }

    /**
     * Delete a skill object from the db
     *
     * @param skill $skill
     * @return bool
     */
    public function delete_skill(skill $skill): bool {
        global $DB;
        return $DB->delete_records('competencygrid_skills', ['id' => $skill->id]);
    }
}
