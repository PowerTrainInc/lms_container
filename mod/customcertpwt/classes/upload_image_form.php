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
 * This file contains the class that handles uploading files.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

namespace mod_customcertpwt;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

/**
 * Handles uploading files.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class upload_image_form extends \moodleform {

    /** @var array the filemanager options */
    protected $filemanageroptions = array();

    /**
     * Form definition.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $this->filemanageroptions = array(
            'maxbytes' => $CFG->maxbytes,
            'subdirs' => 1,
            'accepted_types' => 'image');
        $mform->addElement('filemanager', 'customcertpwtimage', get_string('uploadimage', 'customcertpwt'), '',
            $this->filemanageroptions);

        $this->add_action_buttons();
    }

    /**
     * Fill in the current page data for this customcertpwt.
     */
    public function definition_after_data() {
        $mform = $this->_form;

        // Editing existing instance - copy existing files into draft area.
        $draftitemid = file_get_submitted_draft_itemid('customcertpwtimage');
        file_prepare_draft_area($draftitemid, \context_system::instance()->id, 'mod_customcertpwt', 'image', 0,
            $this->filemanageroptions);
        $element = $mform->getElement('customcertpwtimage');
        $element->setValue($draftitemid);
    }
}
