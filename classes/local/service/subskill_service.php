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
use mod_competencygrid\local\repository\coursereference_repository;
use mod_competencygrid\local\repository\subskill_repository;

/**
 * Class subskill_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subskill_service {
    /** @var subskill_repository */
    private subskill_repository $subskillrepository;

    /**
     * Constructs a new instance of the subskill_service class and initializes the repository.
     */
    public function __construct() {
        $this->subskillrepository = new subskill_repository();
    }

    /**
     * Gets the subskill associated with a given subskill ID.
     *
     * @param int $subskillid The ID of the subskill to retrieve.
     * @return subskill The subskill associated with the provided subskill ID.
     */
    public function get_subskill(int $subskillid): subskill {
        return $this->subskillrepository->get_subskill_by_id($subskillid);
    }

    /**
     * Gets all subskills associated with a given skill.
     *
     * @param skill $skill The skill for which to retrieve subskills.
     * @return subskill[]|null An array of subskills associated with the provided skill, or null if none are found.
     */
    public function get_subskills_by_skill(skill $skill): ?array {
        return $this->subskillrepository->get_subskills_by_skill($skill);
    }

    /**
     * Gets all subskills associated with a given competency grid.
     *
     * @param int $gridid The ID of the competency grid for which to retrieve subskills.
     * @return subskill[]|null An array of subskills associated with the provided grid, or null if none are found.
     */
    public function get_subskills_by_gridid(int $gridid, int $visibility): ?array {
        return $this->subskillrepository->get_subskills_by_gridid($gridid, $visibility);
    }

    /**
     * Get a full subskill object by an id
     * @param int $subskillid
     * @return subskill|null
     */
    public function get_subskill_by_id(int $subskillid): ?subskill {
        return $this->subskillrepository->get_subskill_by_id($subskillid);
    }

    /**
     * Inserts a subskill into the DB and assigns a default name.
     * @param subskill $subskill
     * @return subskill
     */
    public function insert_subskill(subskill $subskill): subskill {
        if (empty($subskill->name)) {
            $subskill->name = get_string('add:subskill:defaulttext', 'mod_competencygrid');
            $subskill->visibility = false; // Default to false so users don't directly see that the grid is being edited.
        }
        $subskillid = $this->subskillrepository->insert_subskill($subskill);
        return $this->subskillrepository->get_subskill_by_id($subskillid);
    }

    /**
     * Update a subskill object in the db.
     * @param subskill $subskill
     * @return bool
     */
    public function update_subskill(subskill $subskill): bool {
        return $this->subskillrepository->update_subskill($subskill);
    }

    /**
     * delete a subskill object from the db.
     * @param subskill $subskill
     * @return bool
     */
    public function delete_subskill(subskill $subskill): bool {
        $levelinstanceservice = new levelinstance_service();
        // Also delete levelinstances.
        foreach ($subskill->levelinstances() as $levelinstance) {
            $levelinstanceservice->delete_levelinstance($levelinstance);
        }
        // Also delete coursereferences.
        $coursereferencerepo = new coursereference_repository();
        $coursereferencerepo->delete_coursereferences_by_subskill($subskill);
        return $this->subskillrepository->delete_subskill($subskill);
    }
}
