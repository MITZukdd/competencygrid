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

use mod_competencygrid\local\entity\skill;
use mod_competencygrid\local\entity\subskill;
use mod_competencygrid\local\repository\skill_repository;
use mod_competencygrid\local\repository\userinput_repository;

/**
 * Class skill_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skill_service {

    /** @var skill_repository */
    private skill_repository $skillrepository;

    /**
     * Constructs a new instance of the skill_service class and initializes the repository.
     */
    public function __construct() {
        $this->skillrepository = new skill_repository();
    }

    /**
     * Gets the skill associated with a given subskill.
     *
     * @param subskill $subskill The subskill for which to retrieve the parent skill.
     * @return skill The skill associated with the provided subskill.
     */
    public function get_skill(subskill $subskill): skill {
        return $this->skillrepository->get_skill_by_subskillid($subskill->id);
    }

    /**
     * Gets all modification times for user inputs associated with a given skill and user.
     *
     * @param int $skillid The skill we want to check.
     * @param int $userid The ID of the user for whom we want to retrieve modification times.
     * @return array<int> A flat array containing the modification times in a 10 digit unix time format
     */
    public function get_modification_times(int $userid, int $skillid): array {
        $userinputrepo = new userinput_repository();
        $times = [];
        $inputs = $userinputrepo->get_userinputs_by_skillid($userid, $skillid);
        foreach ($inputs as $input) {
            $times[] = $input->timemodified;
        }
        return $times;
    }

    /**
     * Inserts a skill into the DB and assigns a default name.
     * @param skill $skill
     * @return skill
     */
    public function insert_skill(skill $skill): skill {
        if (empty($skill->name)) {
            $skill->name = "New skill";
            $skill->visibility = false; // Default to false so users don't directly see that the grid is being edited.
        }
        $skillid = $this->skillrepository->insert_skill($skill);
        return $this->skillrepository->get_skill_by_id($skillid);
    }

    /**
     * Get a full skill object from an id
     * @param int $skillid
     * @return skill|null
     */
    public function get_skill_by_id(int $skillid): ?skill {
        return $this->skillrepository->get_skill_by_id($skillid);
    }

    /**
     * Update a skill object in the db.
     * @param skill $skill
     * @return bool
     */
    public function update_skill(skill $skill): bool {
        return $this->skillrepository->update_skill($skill);
    }

    /**
     * Delete a skill object from the db and
     * @param skill $skill
     * @return bool
     */
    public function delete_skill(skill $skill): bool {
        $subskillservice = new subskill_service();
        foreach ($skill->subskills() as $subskill) {
            $subskillservice->delete_subskill($subskill);
        }
        return $this->skillrepository->delete_skill($skill);
    }
}
