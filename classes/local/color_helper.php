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

namespace mod_competencygrid\local;

/**
 * Pure helpers for mapping a value/max ratio to a display colour.
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class color_helper {

    /**
     * @var int MAX_HUE
     */
    private const MAX_HUE = 170;

    /**
     * HSL colour for a value against a maximum (opaque).
     *
     * @param int $val
     * @param int $max
     * @return string CSS colour (hex for zero, else hsla()).
     */
    public static function calc(int $val, int $max): string {
        if ($val === 0) {
            return '#cccccc';
        }
        $hue = round(($val - 1) / ($max - 1) * self::MAX_HUE);
        return "hsla($hue, 50%, 60%, 1)";
    }

    /**
     * Semi-transparent variant.
     *
     * @param int $val
     * @param int $max
     * @return string CSS colour (hex for zero, else hsla()).
     */
    public static function calc_transparent(int $val, int $max): string {
        if ($val === 0) {
            return '#cccccc80';
        }
        $hue = round(($val - 1) / ($max - 1) * self::MAX_HUE);
        return "hsla($hue, 50%, 60%, 0.5)";
    }
}
