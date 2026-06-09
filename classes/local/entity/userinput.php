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

/**
 * Class userinput
 *
 * @package    mod_competencygrid
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userinput {
    use hydratable;

    /**
     * User input id.
     *
     * @var int
     */
    public int $id;

    /**
     * User id.
     *
     * @var int
     */
    public int $userid;

    /**
     * Course id.
     *
     * @var int
     */
    public int $courseid;

    /**
     * Grid id.
     *
     * @var int
     */
    public int $gridid;

    /**
     * Skill id.
     *
     * @var int
     */
    public int $skillid;

    /**
     * Subskill id.
     *
     * @var int
     */
    public int $subskillid;

    /**
     * Level id.
     *
     * @var int
     */
    public int $levelniveau;

    /**
     * Time created.
     *
     * @var int
     */
    public int $timecreated;

    /**
     * Time modified.
     *
     * @var int
     */
    public int $timemodified;
}
