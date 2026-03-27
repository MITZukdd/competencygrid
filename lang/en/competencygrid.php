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
$string['competencygrid:view'] = 'View Competency Grid';

$string['competencygrid_help'] = 'The Competency Grid activity allows teachers to create a grid of competencies and levels, for students to self-asses themselfs to the appropriate level for each competency. This can be used by students to track their progress during their studies and to identify areas where they may need additional support.';
$string['competencygridfieldset'] = 'Custom properties';
$string['competencygridname'] = 'Competency Grid name';
$string['competencygridname_help'] = 'This is the name of the competency grid instance. It will be displayed on the course page and in the navigation.';
$string['competencygridsettings'] = 'Competency Grid settings';
$string['datapersisted'] = 'Your data was saved successfully.';
$string['modulename'] = 'Competency Grid';
$string['modulenameplural'] = 'Competency Grids';
$string['pluginadministration'] = 'Competency grid administration';
$string['pluginname'] = 'Competency Grid';
$string['templatefile'] = 'Template file';
$string['templatefile_help'] = 'Upload a template file in .csv format that defines the structure of the competency Grid. The file should contain the following columns: Skill, Subskill, Level Descriptions. Each row represents a subskill and its corresponding level descriptions for a specific skill.\n For more info on how the template should look refer to the documentation or see the sample file provided with the plugin.';
