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

$string['add:levelinstance:defaulttext'] = 'Level description : Edit Me!';
$string['add:skill'] = 'Add skill';
$string['add:skill:defaulttext'] = 'New Skill : Edit Me!';
$string['add:subskill'] = 'Add subskill';
$string['add:subskill:defaulttext'] = 'New Subskill : Edit Me!';

$string['alternatefittab'] = 'Alternate Title for the Fit Tab';

$string['chart:bar'] = 'Bar Chart';
$string['chart:historic_bar'] = 'Stacked Bar Chart Historic';
$string['chart:historic_line'] = 'Line Chart Historic';
$string['chart:polar'] = 'Polar Chart';
$string['chart:radar'] = 'Radar Chart';
$string['chart:sum'] = 'Sum';

$string['cleanup_deleted_userinput'] = 'Clean up userinputs, from users which were marked as deleted=1 in the user database table';
$string['cleanup_old_userinput'] = 'Clean up old user data, which is older than {$a} months';

$string['cleanup_retention'] = 'Cleanup retention period (months)';
$string['cleanup_retention_desc'] = 'User input older than this many months will be deleted by the scheduled task.';

$string['competencygrid:addinstance'] = 'Add a new Competency Grid to a course';
$string['competencygrid:edit'] = 'Edit a Competency Grid';
$string['competencygrid:view'] = 'View Competency Grid';

$string['competencygrid_help'] = 'The Competency Grid activity allows teachers to create a grid of competencies and levels, for students to self-asses themselfs to the appropriate level for each competency. This can be used by students to track their progress during their studies and to identify areas where they may need additional support.';
$string['competencygridfieldset'] = 'Custom properties';
$string['competencygridname'] = 'Competency Grid name';
$string['competencygridname_help'] = 'This is the name of the competency grid instance. It will be displayed on the course page and in the navigation.';
$string['competencygridsettings'] = 'Competency Grid settings';
$string['dataerror'] = 'An error occurred, your data could not be saved.';
$string['datapersisted'] = 'Your data was saved successfully.';

$string['delete:skill'] = 'Delete this skill';
$string['delete:skilltext'] = 'Are you sure? This action will permanently remove this skill and all its subskills from the database. Learners will no longer see this skill on the Analysis page and the Fit for the Exam page. If you are unsure about this, please consider using the "hide" button to make the skill invisible to learners during completion of the competency grid.';
$string['delete:subskill'] = 'Delete this subskill';
$string['delete:subskilltext'] = 'Are you sure? This action will permanently remove this subskill from the database. Learners will no longer see this subskill on the Analysis page and the Fit for the Exam page. If you are unsure about this, please consider using the "hide" button to make the subskill invisible to learners during completion of the competency grid.';

$string['error:missingn0option'] = 'You must choose an option on how to handle the 0th knowledge level';
$string['error:missingtemplate'] = 'A template file for the Competency Grid is required.';
$string['error:stringtoolong'] = '{$a->element} {$a->index} is too long. Allowed length is < {$a->allowed} characters.';
$string['error:sufficiencylevel'] = 'Sufficiency Level cannot be greater than the maximum amount of levels';
$string['error:templateempty'] = 'The supplied csv is invalid. It seems that all rows are empty.';
$string['error:templateinvalid'] = 'The supplied csv is invalid. It needs at least three colums (Skill, Subskill, and one level).';
$string['error:toomanylevels'] = 'Is this a joke? Why would you need 10000 different levels?';

$string['intro:analysis'] = 'Analysis Page Intro Text';
$string['intro:chart:default'] = 'Default Chart Type';
$string['intro:chart:defaulthistoric'] = 'Default Historic Chart Type';
$string['intro:chart:options'] = 'Chart Options';

$string['intro:coursereferences'] = '<p>Here you can connect Skills with Moodle Courses. Choose any number of them from the dropdown or use the search. ' .
        'When you are content, click the Submit Button.</p>' .
        '<p>The courses will then appear when filling out the grid and are the basis for the course suggestions of the fit page.</p>';
$string['intro:defaultanalysisintro'] = 'Here you can see an analysis of your current and previous assessments.';
$string['intro:defaultfitinsufficient'] = 'You are still missing some skills required for the Exam. <br>
       Take a look into the overview to see what skills you need to improve, and revisit the courses for the skills. <br>
       Based on your profile, these are the courses you may want to revisit:';
$string['intro:defaultfitintro'] = '<h2>Fit for the Exam?</h2><p>Are you fit for the exam? See the overview down below and see which courses you can revisit.</p>';
$string['intro:defaultfitsufficient'] = 'Great work! You are well prepared for the Exam.';
$string['intro:defaultintro'] = 'Here you can perform your self-assessment. ' .
        'Simply choose the skill and subskill you want to evaluate and choose the best fitting level. ' .
        'When you are finished with your assessment, simply click the "Submit" button. ' .
        'It is not necessary to update each subskill every time. Simply update the ones that did change. ';
$string['intro:fit'] = 'Fit for the Exam Page Intro Text';
$string['intro:textoptions'] = 'Text Options';

$string['modulename'] = 'Competency Grid';
$string['modulename_help'] = '#### Key features
* Learners can quickly self-evaluate as they only need to updated subskills that have changed for them
* Edit and update skill/subskill descriptions easily
* View current and historic data in responsive charts
* Customize parameters such as the text displayed to users, default charts, and when a skill is sufficient

#### Ways to use it
* Accompany long-term learning processes
* Prepare for examinations with instant feedback on what courses learners should take';

$string['modulename_link'] = 'https://github.com/MITZukdd/competencygrid';
$string['modulename_summary'] = 'Create a Competency Grid which provides learners with guided self-evaluation.';
$string['modulename_tip'] = 'You can find an exemplary template file on our GitHub : https://github.com/MITZukdd/competencygrid/blob/main/docs/Template.csv';
$string['modulenameplural'] = 'Competency Grids';

$string['niveau_0_custom_text'] = 'Custom text for Niveau 0';
$string['niveau_0_custom_text_content'] = 'I have not yet learned this skill in my current stage of learning';
$string['niveau_0_label'] = 'Niveau 0 Options';
$string['niveau_0_option1'] = 'Niveau 0 (or similar) is already included in the csv';
$string['niveau_0_option2'] = 'Niveau 0 is not included in the csv.<br> Adds a Niveau 0 with a custom text';
$string['niveau_0_option3'] = 'Niveau 0 is not included in the csv.<br> Adds a Niveau 0 with no text';
$string['pluginadministration'] = 'Competency grid administration';
$string['pluginname'] = 'Competency Grid';

$string['privacy:metadata:competencygrid_userinput'] = 'Information about user inputs to competency grids. This includes the skill, subskill and level, which a user has logged, and the time the entry was created.';
$string['privacy:metadata:competencygrid_userinput:gridid'] = 'The ID of the competency grid that the user worked on.';
$string['privacy:metadata:competencygrid_userinput:levelniveau'] = 'The level the user put in.';
$string['privacy:metadata:competencygrid_userinput:skillid'] = 'The skill which was associated with the input.';
$string['privacy:metadata:competencygrid_userinput:subskillid'] = 'The subskill which was associated with the input.';
$string['privacy:metadata:competencygrid_userinput:timecreated'] = 'The time at which the user performed the input';
$string['privacy:metadata:competencygrid_userinput:userid'] = 'The ID of the user that created the input';

$string['progress'] = 'Progress';

$string['showfit'] = 'Show Fit tab';
$string['showfit_help'] = 'Show or hide the "Fit for the exam tab"';
$string['skill'] = 'Skill';
$string['submitall_hint'] = 'To submit all inputs at once, scroll all the way down, and choose "Submit All"';
$string['sufficiencylevel'] = 'Sufficiency level';
$string['sufficiencylevel_help'] = 'Is used to determine if a learner is adequately prepared.';
$string['tab:analysis'] = 'Analysis';
$string['tab:courseedit'] = 'Edit course references';
$string['tab:fit'] = 'Fit for the Exam?';
$string['templatefile'] = 'Template file';
$string['templatefile_help'] = 'Upload a template file in .csv format that defines the structure of the competency Grid. The file should contain the following columns: Skill, Subskill, Level Descriptions. Each row represents a subskill and its corresponding level descriptions for a specific skill.\n For more info on how the template should look refer to the documentation or see the sample file provided with the plugin.';
$string['total_progress'] = 'Total Progress';

$string['unknownaction'] = 'Unknown action. Could not perform changes.';
