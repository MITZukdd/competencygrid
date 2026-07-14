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
use core\output\inplace_editable;
use core\output\pix_icon;
use PhpXmlRpc\Exception\TypeErrorException;

/**
 * Trait exportable,exports maps data from a inplace_editable to be useful for rendering with a template
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait exportable {
    /**
     * Export this inplace_editable to templatable form
     */
    public function export(): array {

        if (! $this instanceof inplace_editable) {
            throw new TypeErrorException(get_class($this) . " must be an instance of inplace_editable");
        }

        if (!$this->editable) {
            return [
                    'displayvalue' => (string)$this->displayvalue,
            ];
        }

        if ($this->editicon === null) {
            $this->editicon = new pix_icon('t/editstring', (string) $this->edithint);
        }

        return [
                'component' => $this->component,
                'itemtype' => $this->itemtype,
                'itemid' => $this->itemid,
                'displayvalue' => (string)$this->displayvalue,
                'value' => (string)$this->value,
                'edithint' => (string)$this->edithint,
                'editlabel' => (string)$this->editlabel,
                'editicon' => $this->editicon->export_for_pix(),
                'type' => $this->type,
                'options' => $this->options,
                'linkeverything' => $this->get_linkeverything() ? 1 : 0,
        ];
    }
}
