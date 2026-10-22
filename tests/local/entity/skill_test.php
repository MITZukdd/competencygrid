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

/**
 * Contains unit tests for mod_competencygrid.
 *
 * @package     mod_competencygrid
 * @category    test
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_competencygrid\local\entity;

global $CFG;
require_once($CFG->dirroot . '/mod/competencygrid/tests/setup_environment.php');

use mod_competencygrid\local\repository\skill_repository;
use mod_competencygrid\setup_environment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;

defined('MOODLE_INTERNAL') || die();

#[CoversClass(\mod_competencygrid\local\entity\skill::class)]
#[CoversTrait(\mod_competencygrid\local\entity\hydratable::class)]

class skill_test extends \advanced_testcase {

    use setup_environment;

    public function test_skill_creation(): void {
        global $DB;

        $env = $this->setup_db();

        // Data correctly persisted
        $skills = $DB->get_records('competencygrid_skills', [
                'gridid' => $env->instance->id,
        ], 'id ASC');

        $this->assertCount(5, $skills);

        // Check integrity of first entry
        $first = reset($skills);
        $this->assertSame('Hygiene zur Primärprävention und Gesundheitsförderung durchführen', $first->name);
        $this->assertEquals($env->instance->id, $first->gridid);

        // Exactly 5 skills persisted
        $this->assertEquals(5, $DB->count_records('competencygrid_skills'));
    }

    public function test_construction_from_record(): void {
        $skill = skill::from_record( [
                'id'     => 42,
                'gridid' => 7,
                'name'   => 'Hygiene zur Primärprävention...',
        ]);
        $this->assertSame(42, $skill->id);
        $this->assertSame('Hygiene zur Primärprävention...', $skill->name);
    }

    public function test_subskills(): void {
        global $DB;
        $env = $this->setup_db();

        $skillrepo = new skill_repository();
        $skills = $skillrepo->get_skills_by_gridid($env->instance->id);
        $skill = reset($skills);
        $subskills = $skill->subskills();
        $this->assertCount(3, $subskills);
    }

}