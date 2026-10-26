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

namespace mod_competencygrid\output;

use context_module;
use context_system;
use core\output\inplace_editable;
use core_external\external_api;
use mod_competencygrid\local\entity\levelinstance;

/**
 * Class competencygrid_inplace_editable_levelinstance. Creates editable description field for a levelinstance
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencygrid_inplace_editable_levelinstance extends inplace_editable {

    use exportable;

    /**
     * Constructor.
     * @param levelinstance $levelinstance
     */
    public function __construct(levelinstance $levelinstance) {
        $gridid = $levelinstance->subskill()->skill()->gridid;
        $cm = get_coursemodule_from_instance('competencygrid', $gridid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        parent::__construct(
                'mod_competencygrid',
                'levelinstance',
                $levelinstance->id,
                has_capability('mod/competencygrid:edit', $context),
                format_string($levelinstance->description),
                $levelinstance->description,
                'Edit description',
                'Enter new description'
        );
    }

    /**
     * Updates the value in database and returns itself.
     *
     * Called from inplace_editable callback
     *
     * @param int $itemid
     * @param mixed $newvalue
     * @return self
     */
    public static function update(int $itemid, $newvalue): self {
        global $DB;

         // Load the record and find out to which grid it belongs.
        $record = $DB->get_record('competencygrid_levelinstances', ['id' => $itemid], '*', MUST_EXIST);
        $levelinstance = levelinstance::from_record($record);
        $gridid = $levelinstance->subskill()->skill()->gridid;

        // Must call validate_context for course module context.
        // This will both check access and set current context.
        $cm = get_coursemodule_from_instance('competencygrid', $gridid, 0, false, MUST_EXIST);
        external_api::validate_context(context_module::instance($cm->id));

        // Check permission of the user to update this item.
        $modulecontext = context_module::instance($cm->id);
        require_capability('mod/competencygrid:edit', $modulecontext);

        // Clean input and update the record.
        $newvalue = clean_param($newvalue, PARAM_NOTAGS);
        $record->description = $newvalue;
        $DB->update_record('competencygrid_levelinstances', $record);
        // Finally return itself.
        return new self(levelinstance::from_record($record));
    }
}
