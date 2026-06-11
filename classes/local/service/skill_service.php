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

// Use Repositories
use mod_competencygrid\local\entity\subskill;
use mod_competencygrid\local\repository\skill_repository;
use mod_competencygrid\local\repository\subskill_repository;

/**
 * Class skill_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skill_service {

    private skill_repository $skill_repository;

    public function __construct() {
        $this->skill_repository = new skill_repository();
    }

    public function get_skill(subskill $subskill) : skill {
        return $this->skill_repository->get_skill_by_subskillid($subskill->id);
    }
}
