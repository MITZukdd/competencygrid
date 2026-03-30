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
 * TODO describe file settings
 *
 * @package    mod_competencygrid
 * @copyright  2026 MITZ mitz.software@ukdd.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Ensure this is only accessible to site admins.
    // Create a new settings page for the activity plugin.
    $settings = new admin_settingpage('mod_competencygrid', get_string('pluginname', 'mod_competencygrid'));

    if ($ADMIN->fulltree) {
        // Add a color picker for the default text color.
        $settings->add(new admin_setting_configcolourpicker(
            'mod_competencygrid/defaulttextcolor', // Setting name.
            get_string('settings:defaulttextcolor', 'mod_competencygrid'), // Visible name.
            get_string('settings:defaulttextcolordesc', 'mod_competencygrid'), // Description.
            '#2F57B2' // Default value.
        ));

        // Add a color picker for the secondary text color.
        $settings->add(new admin_setting_configcolourpicker(
            'mod_competencygrid/secondarytextcolor',
            get_string('settings:secondarytextcolor', 'mod_competencygrid'),
            get_string('settings:secondarytextcolordesc', 'mod_competencygrid'),
            '#000000'
        ));

        // Add a color picker for the previously selected cards color.
        $settings->add(new admin_setting_configcolourpicker(
            'mod_competencygrid/previouslyselectedcolor',
            get_string('settings:previouslyselectedcolor', 'mod_competencygrid'),
            get_string('settings:previouslyselectedcolordesc', 'mod_competencygrid'),
            '#f2d8bc'
        ));

        // Add a color picker for the current selection color.
        $settings->add(new admin_setting_configcolourpicker(
            'mod_competencygrid/currentselectioncolor',
            get_string('settings:currentselectioncolor', 'mod_competencygrid'),
            get_string('settings:currentselectioncolordesc', 'mod_competencygrid'),
            '#8ce6aa'
        ));
    }
}
