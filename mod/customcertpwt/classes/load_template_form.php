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
 * This file contains the form for loading customcertpwt templates.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

namespace mod_customcertpwt;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir . '/formslib.php');

/**
 * The form for loading customcertpwt templates.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class load_template_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;

        // Get the context.
        $context = $this->_customdata['context'];

        $mform->addElement('header', 'loadtemplateheader', get_string('loadtemplate', 'customcertpwt'));

        // Display a link to the manage templates page.
        if ($context->contextlevel != CONTEXT_SYSTEM && has_capability('mod/customcertpwt:manage', \context_system::instance())) {
            $link = \html_writer::link(new \moodle_url('/mod/customcertpwt/manage_templates.php'),
                get_string('managetemplates', 'customcertpwt'));
            $mform->addElement('static', 'managetemplates', '', $link);
        }

        $templates = $DB->get_records_menu('customcertpwt_templates',
            array('contextid' => \context_system::instance()->id), 'name ASC', 'id, name');
        if ($templates) {
            $group = array();
            $group[] = $mform->createElement('select', 'ltid', '', $templates);
            $group[] = $mform->createElement('submit', 'loadtemplatesubmit', get_string('load', 'customcertpwt'));
            $mform->addElement('group', 'loadtemplategroup', '', $group, '', false);
            $mform->setType('ltid', PARAM_INT);
        } else {
            $msg = \html_writer::tag('div', get_string('notemplates', 'customcertpwt'), array('class' => 'alert'));
            $mform->addElement('static', 'notemplates', '', $msg);
        }
    }
}
