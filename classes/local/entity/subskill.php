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

namespace mod_competencygrid\local\entity;

use mod_competencygrid\local\service\levelinstance_service;
use mod_competencygrid\local\service\skill_service;

/**
 * Class subskill
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subskill {
    use hydratable;

    /**
     * Subskill id.
     *
     * @var int
     */
    public int $id;

    /**
     * Subskill name.
     *
     * @var string
     */
    public string $name;

    /**
     * Skill id.
     *
     * @var int
     */
    public int $skillid;

    /**
     * Visibility of the subskill during filling out.
     * @var bool
     */
    public bool $visibility;

    /**
     * Returns an array of levelinstances for this subskill
     * @return levelinstance[]|null
     */
    public function levelinstances() {
        return (new levelinstance_service())->get_levelinstance_by_subskill($this);
    }

    /**
     * Get the skill of this subskill
     * @return skill
     */
    public function skill() {
        return (new skill_service())->get_skill($this);
    }
}
