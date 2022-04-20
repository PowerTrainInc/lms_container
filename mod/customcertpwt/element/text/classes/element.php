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
 * This file contains the customcertpwt element text's core interaction API.
 *
 * @package    customcertpwtelement_text
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

namespace customcertpwtelement_text;

defined('MOODLE_INTERNAL') || die();

/**
 * The customcertpwt element text's core interaction API.
 *
 * @package    customcertpwtelement_text
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class element extends \mod_customcertpwt\element {

    /**
     * This function renders the form elements when adding a customcertpwt element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        $mform->addElement('textarea', 'text', get_string('text', 'customcertpwtelement_text'));
        $mform->setType('text', PARAM_RAW);
        $mform->addHelpButton('text', 'text', 'customcertpwtelement_text');

        parent::render_form_elements($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * customcertpwt_elements table.
     *
     * @param \stdClass $data the form data
     * @return string the text
     */
    public function save_unique_data($data) {
        return $data->text;
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        \mod_customcertpwt\element_helper::render_content($pdf, $this, $this->get_text());
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        return \mod_customcertpwt\element_helper::render_html_content($this, $this->get_text());
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data())) {
            $element = $mform->getElement('text');
            $element->setValue($this->get_data());
        }
        parent::definition_after_data($mform);
    }

    /**
     * Helper function that returns the text.
     *
     * @return string
     */
    protected function get_text() : string {
        $context = \mod_customcertpwt\element_helper::get_context($this->get_id());
        return format_text($this->get_data(), FORMAT_HTML, ['context' => $context]);
    }
}
