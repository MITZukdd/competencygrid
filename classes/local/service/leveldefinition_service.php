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

use mod_competencygrid\local\repository\leveldefinition_repository;

/**
 * Class skill_service
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leveldefinition_service {

    /** @var leveldefinition_repository */
    private leveldefinition_repository $leveldefinitionrepository;

    /**
     * Constructs a new instance of the skill_service class and initializes the repository.
     */
    public function __construct() {
        $this->leveldefinitionrepository = new leveldefinition_repository();
    }

    /**
     * Returns the count of proper levels of a competency grid (EXCLUDES 0)
     * @param int $gridid
     * @return int
     */
    public function get_level_count(int $gridid) {
        $defs = $this->leveldefinitionrepository->get_leveldefinitions_by_gridid($gridid);
        return count($defs) - 1; // EXCLUDE 0.
    }
}
