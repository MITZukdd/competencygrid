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
 * Admin Settings page.
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_competencygrid', get_string('pluginname', 'mod_competencygrid'));

    if ($ADMIN->fulltree) {
        // Retention period in months.
        $settings->add(new admin_setting_configtext(
            'mod_competencygrid/cleanup_cutoff',
            get_string('cleanup_retention', 'mod_competencygrid'),
            get_string('cleanup_retention_desc', 'mod_competencygrid'),
            144, // Default value 12 years (144 months).
            PARAM_INT
        ));
    }
}
