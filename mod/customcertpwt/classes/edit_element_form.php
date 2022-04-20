<?php
// This file is part of the customcertpwt module for Moodle - http://moodle.org/
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
 * This file contains the form for handling editing a customcertpwt element.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

namespace mod_customcertpwt;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/customcertpwt/includes/colourpicker.php');

\MoodleQuickForm::registerElementType('customcertpwt_colourpicker',
    $CFG->dirroot . '/mod/customcertpwt/includes/colourpicker.php', 'MoodleQuickForm_customcertpwt_colourpicker');

/**
 * The form for handling editing a customcertpwt element.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class edit_element_form extends \moodleform {

    /**
     * @var \mod_customcertpwt\element The element object.
     */
    protected $element;

    /**
     * Form definition.
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->updateAttributes(array('id' => 'editelementform'));

        $element = $this->_customdata['element'];

        // Add the field for the name of the element, this is required for all elements.
        $mform->addElement('text', 'name', get_string('elementname', 'customcertpwt'), 'maxlength="255"');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', get_string('pluginname', 'customcertpwtelement_' . $element->element));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('name', 'elementname', 'customcertpwt');

        $this->element = \mod_customcertpwt\element_factory::get_element_instance($element);
        $this->element->set_edit_element_form($this);
        $this->element->render_form_elements($mform);

        $this->add_action_buttons(true);
    }

    /**
     * Fill in the current page data for this customcertpwt.
     */
    public function definition_after_data() {
        $this->element->definition_after_data($this->_form);
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        $errors = array();

        if (\core_text::strlen($data['name']) > 255) {
            $errors['name'] = get_string('nametoolong', 'customcertpwt');
        }

        $errors += $this->element->validate_form_elements($data, $files);

        return $errors;
    }
}
