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

// Use entities
use mod_competencygrid\local\entity\skill;
use mod_competencygrid\local\entity\subskill;

// Use Repositories
use mod_competencygrid\local\repository\skill_repository;
use mod_competencygrid\local\repository\subskill_repository;

/**
 * Class subskill_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subskill_service {

    private subskill_repository $subskill_repository;

    public function __construct() {
        $this->subskill_repository = new subskill_repository();
    }

    public function get_subskill(int $subskillid) : subskill {
        return $this->subskill_repository->get_subskill_by_id($subskillid);
    }

    public function get_subskills(skill $skill) : ?array {
        return $this->subskill_repository->get_subskills_by_skillid($skill->id);
    }
}
