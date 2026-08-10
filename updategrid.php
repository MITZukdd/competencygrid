<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Add Subskill page for mod_competencygrid.
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_competencygrid\local\entity\skill;
use mod_competencygrid\local\entity\subskill;
use mod_competencygrid\local\service\levelinstance_service;
use mod_competencygrid\local\service\skill_service;
use mod_competencygrid\local\service\subskill_service;

require(__DIR__ . '/../../config.php');

$gridid    = required_param('gridid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$skillid = optional_param('skillid', -1, PARAM_INT);
$subskillid = optional_param('subskillid', -1, PARAM_INT);
$visibility = optional_param('visibility', 1, PARAM_BOOL);


$cm = get_coursemodule_from_instance('competencygrid', $gridid, 0, 0, MUST_EXIST );
$context = context_module::instance($cm->id);
require_login($cm->course, false, $cm);
require_capability('mod/competencygrid:edit', $context);

match($action) {
    'addskill' => addskill($cm, $gridid),
    'addsubskill' => addsubskill($cm, $gridid, $skillid),
    'delete' => delete_skill_or_subskill($cm, $gridid, $skillid, $subskillid),
    'setvisibility' => setvisibility($cm, $gridid, $visibility, $skillid, $subskillid),
    default => redirect(
            new moodle_url('/mod/competencygrid/gridedit.php', ['id' => $cm->id]),
            get_string('unknownaction', 'mod_competencygrid')
    ),
};

/**
 *
 * @param $cm
 * @param int $gridid
 * @return void
 */
function addskill($cm, int $gridid) {
    if ($gridid < 0) {
        redirect_error($cm);
    }
    $skillservice = new skill_service();
    $skill = new skill();
    $skill->gridid = $gridid;
    $skillservice->insert_skill($skill);
    redirect_success($cm);
}

/**
 * Add new subskill to the grid.
 * @param $cm
 * @param int $gridid
 * @param int $skillid
 * @return void
 */
function addsubskill($cm, int $gridid, int $skillid) {
    if ($skillid < 0 || $gridid < 0) {
        redirect_error($cm);
    }
    $subskillservice = new subskill_service();
    $levelinstanceservice = new levelinstance_service();
    // Insert the new subskill with default values.
    $subskill = new subskill();
    $subskill->skillid = $skillid;
    $subskill = $subskillservice->insert_subskill($subskill);
    // Add default levelinstances for the subskill.
    $levelinstanceservice->insert_levelinstances($subskill);
    redirect_success($cm);
}

/**
 * Sets the visibility of either a Skill or Subskill, but not both at the same time.
 * @param $cm
 * @param int $gridid
 * @param int $visibility
 * @param int $skillid
 * @param int $subskillid
 * @return void
 */
function setvisibility($cm, int $gridid, int $visibility, int $skillid=0, int $subskillid = 0) {
    $skillservice = new skill_service();
    $subskillservice = new subskill_service();
    $skill = $skillservice->get_skill_by_id($skillid);
    if ($subskillid > 0) {
        $subskill = $subskillservice->get_subskill_by_id($subskillid);
    }

    // ERROR HANDLING.
    // Nothing chosen.
    if ($skillid < 0 && $subskillid < 0) {
        redirect_error($cm);
    }
    // Skill/Subskill not found.
    if ($skill === null) {
        redirect_error($cm);
    }
    if ($subskillid > 0) {
        if ($subskill === null) {
            redirect_error($cm);
        }
        // Mismatch : subskill doesnt belong to skill.
        if ($subskill->skill()->id != $skill->id) {
            redirect_error($cm);
        }
    }

    // Mismatch : skill doesnt belong to this grid.
    if ($skill->gridid != $gridid) {
        redirect_error($cm);
    }
    // Invalid parameters for visibility.
    if ($visibility < 0 || $visibility > 1 ) {
        redirect_error($cm);
    }

    // SET VISIBILITY.
    if ($subskillid > 0) {
        $subskill->visibility = $visibility;
        $result = $subskillservice->update_subskill($subskill);
        $result ? redirect_success($cm) : redirect_error($cm);
    }
    if ($skillid > 0) {
        $skill->visibility = $visibility;
        $result = $skillservice->update_skill($skill);
        $result ? redirect_success($cm) : redirect_error($cm);
    }
    redirect_error($cm);
}

/**
 * Initiates deletion of either a singular subskill with its levelinstances + coursereferences, or of an entire skill
 * with associated subskill
 * @param $cm
 * @param int $gridid
 * @param int $skillid
 * @param int $subskillid
 * @return void
 */
function delete_skill_or_subskill($cm, int $gridid, int $skillid=0, int $subskillid = 0) {
    $skillservice = new skill_service();
    $subskillservice = new subskill_service();
    $skill = $skillservice->get_skill_by_id($skillid);
    if ($subskillid > 0) {
        $subskill = $subskillservice->get_subskill_by_id($subskillid);
    }

    // ERROR HANDLING.
    // Nothing chosen.
    if ($skillid < 0 && $subskillid < 0) {
        redirect_error($cm);
    }
    // Skill/Subskill not found.
    if ($skill === null) {
        redirect_error($cm);
    }
    if ($subskillid > 0) {
        if ($subskill === null) {
            redirect_error($cm);
        }
        // Mismatch : subskill doesnt belong to skill.
        if ($subskill->skill()->id != $skill->id) {
            redirect_error($cm);
        }
    }

    // Mismatch : skill doesnt belong to this grid.
    if ($skill->gridid != $gridid) {
        redirect_error($cm);
    }

    // SET VISIBILITY.
    if ($subskillid > 0) {
        $result = $subskillservice->delete_subskill($subskill);
        $result ? redirect_success($cm) : redirect_error($cm);
    }
    if ($skillid > 0) {
        $result = $skillservice->delete_skill($skill);
        $result ? redirect_success($cm) : redirect_error($cm);
    }
    redirect_error($cm);
}

/**
 * Redirect with an error message
 * @param $cm
 * @return void
 */
function redirect_error($cm) {
    redirect(new moodle_url('/mod/competencygrid/gridedit.php', ['id' => $cm->id]), get_string('dataerror', 'mod_competencygrid'));
}

/**
 * Redirect with a success message
 * @param $cm
 * @return void
 */
function redirect_success($cm) {
    redirect(new moodle_url('/mod/competencygrid/gridedit.php',
            ['id' => $cm->id]), get_string('datapersisted', 'mod_competencygrid'));
}
