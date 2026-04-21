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
        global $CFG;

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
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Again, only sshow this when creating a new instance, not when updating.
        if ($this->_instance == "") {
            $options = [
                0 => get_string('niveau_0_option1', 'mod_competencygrid'),
                1 => get_string('niveau_0_option2', 'mod_competencygrid'),
                2 => get_string('niveau_0_option3', 'mod_competencygrid'),
            ];

            $mform->addElement('select', 'niveau0option', get_string('niveau_0_label', 'mod_competencygrid'), $options);
            $mform->setType('niveau0option', PARAM_INT);
            $mform->setDefault('niveau0option', 0);

            $mform->addElement('textarea', 'niveau0customtext', get_string('niveau_0_custom_text', 'mod_competencygrid'), ['rows' => 4, 'cols' => 60]);
            $mform->setType('niveau0customtext', PARAM_TEXT);
            $mform->setDefault('niveau0customtext', get_string('niveau_0_custom_text_content', 'mod_competencygrid'));

            // Only show this if user wants to add a custom niveau 0 text.
            $mform->hideIf('niveau0customtext', 'niveau0option', 'neq', 1);
        }

        // Adding the rest of mod_competencygrid settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        // $mform->addElement('static', 'label1', 'competencygridsettings', get_string('competencygridsettings', 'mod_competencygrid'));
        // $mform->addElement('header', 'competencygridfieldset', get_string('competencygridfieldset', 'mod_competencygrid'));

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
