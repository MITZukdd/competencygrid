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

use mod_competencygrid\local\service\subskill_service;

/**
 * Class levelinstance
 *
 * @package    mod_competencygrid
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelinstance {
    use hydratable;

    /**
     * Instance ID.
     *
     * @var int
     */
    public int $id;

    /**
     * Level description.
     *
     * @var string
     */
    public string $description;

    /**
     * Numeric representation of the level.
     *
     * @var int
     */
    public int $niveaunumeric;

    /**
     * Related subskill ID.
     *
     * @var int
     */
    public int $subskillid;

    /**
     * The subskill this levelinstance belongs to
     * @return subskill
     */
    public function subskill(): subskill {
        return (new subskill_service())->get_subskill($this->subskillid);
    }
}
