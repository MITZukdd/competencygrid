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

use mod_competencygrid\local\entity\subskill;
use mod_competencygrid\local\service\subskill_service;

/**
 * Class skill
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skill {
    use hydratable;

    /**
     * Skill id.
     *
     * @var int
     */
    public int $id;

    /**
     * Skill name.
     *
     * @var string
     */
    public string $name;

    /**
     * Competency grid id.
     *
     * @var int
     */
    public int $gridid;

    /**
     * Returns an array of subskills for a skill
     * @return subskill[]|null
     */
    public function subskills() {
        return (new subskill_service())->get_subskills_by_skill($this);
    }
}
