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
 * Covers setup and teardown after activity deletion.
 *
 * @package     mod_competencygrid
 * @category    test
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_competencygrid;

global $CFG;

use PHPUnit\Framework\Attributes\CoversFunction;

require_once($CFG->dirroot . '/mod/competencygrid/tests/setup_environment.php');
require_once($CFG->dirroot . '/course/lib.php');


defined('MOODLE_INTERNAL') || die();

#[CoversFunction('competencygrid_add_instance')]
#[CoversFunction('competencygrid_delete_instance')]
#[CoversFunction('competencygrid_persist_uploaded_csv_file')]
class grid_lifecycle_test extends \advanced_testcase {

    use setup_environment;

    public function test_activity_creation() {
        global $DB;
        $env = $this->setup_db(); // Setup with deafult options + correct template.

        // Check right amount of entries
        $this->assertEquals(5, $DB->count_records('competencygrid_skills'));
        $this->assertEquals(27, $DB->count_records('competencygrid_subskills'));
        $this->assertEquals(5, $DB->count_records('competencygrid_leveldefinitions'));
        $this->assertEquals(135, $DB->count_records('competencygrid_levelinstances'));

        // Check correct assignment for one skill

    }

    public function test_delete_instance_removes_all_data(): void {
        global $DB;

        $env = $this->setup_db();
        $gridid = $env->instance->id;
        $contextid = $env->context->id;

        $user = self::getDataGenerator()->create_user();
        $this->load_user_inputs_to_db($env, $user);

        // Check data was actually created.
        $this->assertTrue($DB->record_exists('competencygrid', ['id' => $gridid]));
        $this->assertEquals(5, $DB->count_records('competencygrid_skills', [
                'gridid' => $gridid,
        ]));

        // Collect skillids, subskillids before delete
        $skillids = $DB->get_fieldset_select('competencygrid_skills', 'id', 'gridid = ?', [$gridid]);

        [$insql, $params] = $DB->get_in_or_equal($skillids);
        $subskillids = $DB->get_fieldset_select(
                'competencygrid_subskills', 'id', "skillid $insql", $params
        );

        $this->assertNotEmpty($skillids, 'Keine Skillswurden persistiert.');
        $this->assertNotEmpty($subskillids, 'Keine Subskills wurden persistiert.');

        // Trigger instance deletion.
        course_delete_module($env->cm->id);

        // Verify empty competencygrid tables.
        $this->assertFalse($DB->record_exists('competencygrid', ['id' => $gridid]));
        $this->assertEquals(0, $DB->count_records('competencygrid_skills', ['gridid' => $gridid]));
        $this->assertEquals(0, $DB->count_records('competencygrid_userinput', ['gridid' => $gridid]));
        $this->assertEquals(0, $DB->count_records('competencygrid_leveldefinitions', ['gridid' => $gridid]));


        [$insql, $params] = $DB->get_in_or_equal($skillids);
        $this->assertEquals(0, $DB->count_records_select(
                'competencygrid_subskills', "skillid $insql", $params
        ));

        [$insql, $params] = $DB->get_in_or_equal($subskillids);
        $this->assertEquals(0, $DB->count_records_select(
                'competencygrid_levelinstances', "subskillid $insql", $params
        ));

        // Verify deletion in context and course modules
        $this->assertFalse($DB->record_exists('course_modules', ['id' => $env->cm->id]));
        $this->assertFalse($DB->record_exists('context', ['id' => $contextid]));
    }


}