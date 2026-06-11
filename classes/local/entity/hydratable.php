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
 * Trait hydratable, automatically maps data attributes from a DB query to corresponding properties of an object
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait hydratable {
    /**
     * Populate this object's properties from an stdClass or array.
     */
    public static function from_record($record): static {
        $instance = new static();
        $data = is_object($record) ? get_object_vars($record) : $record;

        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }

    /**
     * Hydrate a list of records.
     * @return static[]
     */
    public static function from_records(iterable $records): array {
        $result = [];
        foreach ($records as $record) {
            $obj = static::from_record($record);
            $result[$obj->id ?? count($result)] = $obj;
        }
        return $result;
    }
}
