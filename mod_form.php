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
 * The main mod_competencygrid configuration form.
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_competencygrid
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_competencygrid_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('competencygridname', 'mod_competencygrid'), ['size' => '64']);

        // Only allow uploading a new template file when adding, not when updating.
        if ($this->_instance == "") {
            $mform->addElement('filepicker', 'templatefile', get_string('templatefile', 'mod_competencygrid'), null, [
            'accepted_types' => ['.csv'],
            ]);
            $mform->addRule('templatefile', get_string('required'), 'required', null, 'client');
        }

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'competencygridname', 'mod_competencygrid');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Sufficiency Level.
        $mform->addElement('text', 'sufficiencylevel', get_string('sufficiencylevel', 'mod_competencygrid'));
        $mform->setType('sufficiencylevel', PARAM_INT);
        $mform->addRule('sufficiencylevel', 'Must be numerical', 'numeric', null, 'client');
        $mform->addHelpButton('sufficiencylevel', 'sufficiencylevel', 'mod_competencygrid');
        $mform->setDefault('sufficiencylevel', 0);

        // Hide/Show Fit tab.
        $mform->addElement('selectyesno', 'showfit', get_string('showfit', 'mod_competencygrid'));
        $mform->setType('showfit', PARAM_BOOL);
        $mform->setDefault('showfit', 1);

        $mform->addHelpButton('showfit', 'showfit', 'mod_competencygrid');

        // Options for adding a 0th Niveau.
        // Again, only sshow this when creating a new instance, not when updating.
        if ($this->_instance == "") {
            $options = [
                -1 => '',
                0 => get_string('niveau_0_option1', 'mod_competencygrid'),
                1 => get_string('niveau_0_option2', 'mod_competencygrid'),
                2 => get_string('niveau_0_option3', 'mod_competencygrid'),
            ];

            $mform->addElement('select', 'niveau0option', get_string('niveau_0_label', 'mod_competencygrid'), $options);
            $mform->setType('niveau0option', PARAM_INT);
            $mform->addRule('niveau0option', get_string('required'), 'required', null, 'client');

            $mform->addElement(
                    'textarea',
                    'niveau0customtext',
                    get_string('niveau_0_custom_text', 'mod_competencygrid'),
                    ['rows' => 4, 'cols' => 60]);
            $mform->setType('niveau0customtext', PARAM_TEXT);
            $mform->setDefault(
                    'niveau0customtext',
                    get_string('niveau_0_custom_text_content', 'mod_competencygrid'));

            // Only show this if user wants to add a custom niveau 0 text.
            $mform->hideIf('niveau0customtext', 'niveau0option', 'neq', 1);
        }

        // Intro Text Editors.
        $mform->addElement('header', 'textoptions', get_string('intro:textoptions', 'mod_competencygrid'));

        // Fit page Intro.
        $mform->addElement('editor', 'fitintro', get_string('intro:fit', 'mod_competencygrid'),
                ['rows' => 10],
                ['maxfiles' => 0, 'noclean' => true, 'context' => $this->context, 'subdirs' => true],
        );
        $mform->setType('fitintro', PARAM_RAW);

        // Analysis page Intro.
        $mform->addElement('editor', 'analysisintro', get_string('intro:analysis', 'mod_competencygrid'),
                ['rows' => 10],
                ['maxfiles' => 0, 'noclean' => true, 'context' => $this->context, 'subdirs' => true],
        );
        $mform->setType('analysisintro', PARAM_RAW);

        // Alternate Tab Heading.
        $mform->addElement('text', 'fittab', get_string('alternatefittab', 'mod_competencygrid'), ['maxlength' => 30, 'size' => 30]);
        $mform->setType('fittab', PARAM_TEXT);
        $mform->setDefault('fittab', get_string('tab:fit', 'mod_competencygrid'));
        $mform->addRule('fittab', get_string('maximumchars', '', 30), 'maxlength', 30, 'client');

        // CHART OPTIONS.
        $mform->addElement('header', 'chartoptions', get_string('intro:chart:options', 'mod_competencygrid'));

        $chartoptions = [
                'radar' => get_string('chart:radar', 'mod_competencygrid'),
                'polar' => get_string('chart:polar', 'mod_competencygrid'),
                'bar'   => get_string('chart:bar', 'mod_competencygrid'),
        ];
        $mform->addElement('select', 'defaultchart',
                get_string('intro:chart:default', 'mod_competencygrid'), $chartoptions);
        $mform->setType('defaultchart', PARAM_TEXT);
        $mform->setDefault('defaultchart', 'radar');

        $historicoptions = [
                'historic-bar'  => get_string('chart:historic_bar', 'mod_competencygrid'),
                'historic-line' => get_string('chart:historic_line', 'mod_competencygrid'),
        ];
        $mform->addElement('select', 'defaulthistoricchart',
                get_string('intro:chart:defaulthistoric', 'mod_competencygrid'), $historicoptions);
        $mform->setType('defaulthistoricchart', PARAM_TEXT);
        $mform->setDefault('defaulthistoricchart', 'historic-bar');

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Preprocess editor fields as they have arrays as default values
     * @param $defaultvalues
     * @return void
     * @throws coding_exception
     */
    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        parent::data_preprocessing($defaultvalues);
        if (empty($this->_instance)) {
            if (empty($defaultvalues['introeditor']['text'])) {
                $defaultvalues['introeditor']['text']   = get_string('intro:defaultintro', 'mod_competencygrid');
            }
            if (empty($defaultvalues['fitintro']['text'])) {
                $defaultvalues['fitintro']['text']   = get_string('intro:defaultfitintro', 'mod_competencygrid');
            }
            if (empty($defaultvalues['analysisintro']['text'])) {
                $defaultvalues['analysisintro']['text']   = get_string('intro:defaultanalysisintro', 'mod_competencygrid');
            }
        } else {
            $fitintro = $DB->get_field('competencygrid', 'fitintro', ['id' => $this->_instance]);
            if (!is_array($defaultvalues['fitintro'])) {
                $defaultvalues['fitintro'] = [];
            }
            $defaultvalues['fitintro']['text']   = $fitintro;
            $defaultvalues['fitintro']['format'] = $DB->get_field('competencygrid', 'fitintroformat', ['id' => $this->_instance]);;

            $analysisintro = $DB->get_field('competencygrid', 'analysisintro', ['id' => $this->_instance]);
            if (!is_array($defaultvalues['analysisintro'])) {
                $defaultvalues['analysisintro'] = [];
            }
            $defaultvalues['analysisintro']['text']   = $analysisintro;
            $defaultvalues['analysisintro']['format'] = $DB->get_field('competencygrid', 'analysisintroformat', ['id' => $this->_instance]);;
        }
    }


    /**
     * Validate the Form
     * @param $data
     * @param $files
     * @return array|error[]
     * @throws coding_exception
     */
    public function validation($data, $files) {
        global $CFG, $USER;
        $errors = parent::validation($data, $files);

        // When editing existing instance, check wheter there are that many levels at all.
        if ($this->_instance != "") {
            $leveldefinitionservice = new \mod_competencygrid\local\service\leveldefinition_service();
            $numlevels = $leveldefinitionservice->get_level_count($this->_instance);
            if ($data['sufficiencylevel'] > $numlevels) {
                $errors['sufficiencylevel'] = get_string('error:sufficiencylevel', 'mod_competencygrid');
            }
        }

        // Only needs to be validated when creating new grid, not when editing.
        if ($this->_instance == "") {
            $niveau0option = $data['niveau0option'];
            if ($niveau0option < 0) {
                $errors['niveau0option'] = get_string('error:missingn0option', 'mod_competencygrid');
            }

            $draftitemid = $data['templatefile'];
            if (empty($draftitemid)) {
                $errors['templatefile'] = get_string('error:missingtemplate', 'mod_competencygrid');
                return $errors;
            }

            // Files in the draft area live in the current users context.
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();

            $draftfiles = $fs->get_area_files(
                    $usercontext->id,
                    'user',
                    'draft',
                    $draftitemid,
                    'id',
                    false
            );

            if (empty($draftfiles)) {
                // No file uploaded.
                $errors['templatefile'] = get_string('error:missingtemplate', 'mod_competencygrid');
                return $errors;
            }

            // Get content of file.
            $file = reset($draftfiles);
            $content = $file->get_content();

            // Perform validation of file.
            $errors = array_merge($errors, $this->validate_csv($content, $data['sufficiencylevel']));
        }

        return $errors;
    }

    /**
     * Return an array of error messages for the given csv input.
     * @param string $content
     * @param int $sufficiencylevel
     * @return error[] array of errors
     */
    private function validate_csv(string $content, int $sufficiencylevel): array {

        $errors = [];
        $lines = explode("\n", $content);

        // Check header.
        $header = array_shift($lines);
        $header = trim($header);
        $header = str_getcsv($header);

        if ($this->_instance == "") {
            // Only needs to be checked here when creating new instance.
            // Check for existing instance is performed in validate().
            if ($sufficiencylevel > count($header)) {
                $errors['sufficiencylevel'] = get_string('error:sufficiencylevel', 'mod_competencygrid');
            }
        }

        // Check >= 3 colums (skill, subskill, and at least one niveau).
        if (count($header) < 3) {
            $errors['templatefile'] = get_string('error:templateinvalid', 'mod_competencygrid');
            return $errors;
        }

        // Could be 9'999'999'999 in theory (int 10) but it should be less than 10000 anyway.
        if (count($header) > 10002) {
            $errors['templatefile'] = get_string('error:toomanylevels', 'mod_competencygrid');
            return $errors;
        }

        foreach ($header as $i => $field) {

            if ($i < 2) {
                continue; // We do not care about the first 2 colums, as they will be replaced by the language strings.
            }

            // Check leveldescription < 1023 chars.
            if (mb_strlen($field) > 1023 ) {
                $errors['templatefile'] = get_string('error:stringtoolong', 'mod_competencygrid', [
                        'element' => 'Top Level Description',
                        'index' => ($i + 1),
                        'allowed' => 1023,
                ]);
                return $errors;
            }
        }

        foreach ($lines as $index => $line) {
            $line = trim($line);
            $line = str_getcsv($line);

            // Check skill / subskill colums have < 255 chars.
            if (mb_strlen(array_shift($line)) > 255 ) {
                $errors['templatefile'] = get_string('error:stringtoolong', 'mod_competencygrid', [
                        'element' => 'Skill name',
                        'index' => ($index + 1),
                        'allowed' => 255,
                ]);
                return $errors;
            }

            if (mb_strlen(array_shift($line)) > 255 ) {
                $errors['templatefile'] = get_string('error:stringtoolong', 'mod_competencygrid', [
                        'element' => 'Subskill name',
                        'index' => ($index + 1),
                        'allowed' => 255,
                ]);
                return $errors;
            }

            // Check levelinstance < 1023 chars.
            foreach ($line as $col) {

                if (mb_strlen($col) > 1023 ) {
                    $errors['templatefile'] = get_string('error:stringtoolong', 'mod_competencygrid', [
                            'element' => 'A Level description for subskill',
                            'index' => ($index + 1),
                            'allowed' => 1023,
                    ]);
                    return $errors;
                }
            }
        }
        return $errors;
    }
}
