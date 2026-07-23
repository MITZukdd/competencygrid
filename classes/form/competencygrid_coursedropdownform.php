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

namespace mod_competencygrid\form;

use mod_competencygrid\local\repository\coursereference_repository;
use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');
/**
 * Class competencygrid_coursedropdownform
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencygrid_coursedropdownform extends moodleform {
    /** @var int[] List of subskill IDs this form was built for. */
    private array $subskillids = [];

    /**
     * Define form elements.
     *
     * Builds the course selection dropdowns for each subskill in the provided
     * render data. Sets defaults from the competencygrid_coursereferences table.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $renderdata = $this->_customdata['renderdata'];

        foreach ($renderdata as $skilldata) {
            $mform->addElement('header', 'Header' . $skilldata['skill_index'], $skilldata['qualification_title']);
            $mform->setExpanded('Header' . $skilldata['skill_index'], false);

            foreach ($skilldata['subskills'] as $subskilldata) {
                $subskillid = $subskilldata['subskill_id'];
                $this->subskillids[] = $subskillid;

                $name = 'courseids' . $subskillid;
                $mform->addElement('course', $name, $subskilldata['name'], [
                        'multiple' => true,
                        'limittoenrolled' => false,
                        'includefrontpage' => false,
                        'onlywithcompletion' => false,
                ]);

                $mform->setType($name, PARAM_INT);

                $coursereferencerepository = new coursereference_repository();
                $courseids = $coursereferencerepository->get_coursereferenceids_by_subskillid($subskillid);

                $mform->setDefault($name, $courseids);
            }
        }
        $this->add_action_buttons();
    }

    /**
     * Get selected course IDs per subskill.
     *
     * Returns an associative array where keys are subskill IDs and values are
     * arrays of selected course IDs (integers). If no data was submitted or
     * no selections exist for a subskill the value will be an empty array.
     *
     * @return int[][] Associative array of subskillid => array of course ids
     */
    public function get_selections(): array {
        $data = $this->get_data();
        if (!$data) {
            return [];
        }
        $result = [];
        foreach ($this->subskillids as $subskillid) {
            $key = 'courseids' . $subskillid;
            $value = $data->$key ?? null;
            $result[$subskillid] = is_array($value) ? array_map('intval', $value) : [];
        }
        return $result;
    }
}
