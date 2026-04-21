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
 * Plugin strings are defined here.
 *
 * @package     mod_competencygrid
 * @category    string
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['competencygrid:addinstance'] = 'Add a new Competency Grid to a course';
$string['competencygrid:edit'] = 'Edit a Competency Grid';
$string['competencygrid:view'] = 'View Competency Grid';

$string['competencygrid_help'] = 'The Competency Grid activity allows teachers to create a grid of competencies and levels, for students to self-asses themselfs to the appropriate level for each competency. This can be used by students to track their progress during their studies and to identify areas where they may need additional support.';
$string['competencygridfieldset'] = 'Custom properties';
$string['competencygridname'] = 'Competency Grid name';
$string['competencygridname_help'] = 'This is the name of the competency grid instance. It will be displayed on the course page and in the navigation.';
$string['competencygridsettings'] = 'Competency Grid settings';
$string['datapersisted'] = 'Your data was saved successfully.';
$string['modulename'] = 'Competency Grid';
$string['modulenameplural'] = 'Competency Grids';
$string['niveau_0_custom_text'] = 'Custom text for Niveau 0';
$string['niveau_0_custom_text_content'] = 'I have not yet learned this skill in my current stage of learning';
$string['niveau_0_label'] = 'Niveau 0 Options';
$string['niveau_0_option1'] = 'Niveau 0 (or similar) is already included in the csv';
$string['niveau_0_option2'] = 'Niveau 0 is not included in the csv.<br> Adds a Niveau 0 with a custom text';
$string['niveau_0_option3'] = 'Niveau 0 is not included in the csv.<br> Adds a Niveau 0 with no text';
$string['pluginadministration'] = 'Competency grid administration';
$string['pluginname'] = 'Competency Grid';
$string['settings:currentselectioncolor'] = 'Cell background color for currently selected cells';
$string['settings:currentselectioncolordesc'] = 'Pick the color that will appear as the cell background for currently selected cells in the grid';
$string['settings:defaulttextcolor'] = 'Color for non-selected grid cells';
$string['settings:defaulttextcolordesc'] = 'Pick the color in which text appears in non-selected cells by default';
$string['settings:previouslyselectedcolor'] = 'Cell background color for previous user input';
$string['settings:previouslyselectedcolordesc'] = 'Pick the color that will appear as the cell background for previous user input';
$string['settings:secondarytextcolor'] = 'Color for selected grid cells';
$string['settings:secondarytextcolordesc'] = 'Pick the color in which text appears in selected cells by default';
$string['submitall_hint'] = 'To submit all inputs at once, scroll all the way down, and choose "Submit All"';
$string['tab:analysis'] = 'Analysis';
$string['tab:fit'] = 'Fit for the OSCE?';
$string['templatefile'] = 'Template file';
$string['templatefile_help'] = 'Upload a template file in .csv format that defines the structure of the competency Grid. The file should contain the following columns: Skill, Subskill, Level Descriptions. Each row represents a subskill and its corresponding level descriptions for a specific skill.\n For more info on how the template should look refer to the documentation or see the sample file provided with the plugin.';
