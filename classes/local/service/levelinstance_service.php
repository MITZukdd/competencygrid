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

use mod_competencygrid\local\entity\levelinstance;
use mod_competencygrid\local\entity\subskill;
use mod_competencygrid\local\repository\levelinstance_repository;

/**
 * Class levelinstance_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelinstance_service {

    /** @var levelinstance_repository */
    private levelinstance_repository $levelinstancerepository;

    /**
     * Constructs a new instance of the skill_service class and initializes the repository.
     */
    public function __construct() {
        $this->levelinstancerepository = new levelinstance_repository();
    }


    /**
     * Returns an array of levelinstances for a subskill
     * @return levelinstance[]|null
     */
    public function get_levelinstance_by_subskill(subskill $subskill): ?array {
        return $this->levelinstancerepository->get_levelinstances_by_subskillid($subskill->id);
    }

    /**
     * Insert as many levelinstances in the db as needed for a subskill.
     * @param subskill $subskill
     * @return bool
     */
    public function insert_levelinstances(subskill $subskill): bool {
        $numlevels = (new leveldefinition_service())->get_level_count($subskill->skill()->gridid);
        for ($i = 0; $i <= $numlevels; $i++) {
            $levelinstance = new levelinstance();
            $levelinstance->description = "New levelinstance description";
            $levelinstance->subskillid = $subskill->id;
            $levelinstance->niveaunumeric = $i;
            $this->levelinstancerepository->insert_levelinstance($levelinstance);
        }
        return true;
    }

    /**
     * Deletes a levelinstance from the db
     * @param levelinstance $levelinstance
     * @return bool
     */
    public function delete_levelinstance(levelinstance $levelinstance): bool {
        return $this->levelinstancerepository->delete_levelinstance($levelinstance);
    }
}
