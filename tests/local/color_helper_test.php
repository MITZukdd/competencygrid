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
namespace mod_competencygrid\local;

use PHPUnit\Framework\Attributes\CoversClass;

defined('MOODLE_INTERNAL') || die();

#[CoversClass(\mod_competencygrid\local\color_helper::class)]
class color_helper_test extends \basic_testcase {

    public function test_calc_value_zero_returns_grey(): void {
        $this->assertSame('#cccccc', color_helper::calc(0, 5));
    }

    public function test_calc_transparent_value_zero_returns_grey_alpha(): void {
        $this->assertSame('#cccccc80', color_helper::calc_transparent(0, 5));
    }

    public function test_calc_value_one_is_hue_zero(): void {
        $this->assertSame('hsla(0, 50%, 60%, 1)', color_helper::calc(1, 5));
    }

    public function test_calc_transparent_value_one_is_hue_zero(): void {
        $this->assertSame('hsla(0, 50%, 60%, 0.5)', color_helper::calc_transparent(1, 5));
    }

    public function test_calc_max_value_is_hue_170(): void {
        $this->assertSame('hsla(170, 50%, 60%, 1)', color_helper::calc(5, 5));
    }

    public function test_calc_tranparent_max_value_is_hue_170(): void {
        $this->assertSame('hsla(170, 50%, 60%, 0.5)', color_helper::calc_transparent(5, 5));
    }

    public function test_calc_max_1(): void {
        $this->assertSame('#cccccc', color_helper::calc(0, 1));
        $this->assertSame('hsla(170, 50%, 60%, 1)', color_helper::calc(1, 1));
    }

    public function test_calc_transparent_max_1(): void {
        $this->assertSame('#cccccc80', color_helper::calc_transparent(0, 1));
        $this->assertSame('hsla(170, 50%, 60%, 0.5)', color_helper::calc_transparent(1, 1));
    }

}