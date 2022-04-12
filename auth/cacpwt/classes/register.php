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
 * PowerTrain CAC Plugin Register Class
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * CAC Registration Form
 */
class cacpwt_register extends moodleform {
    /**
     * Form definition function.
     */
    public function definition() {
        global $SESSION;

        $mform = $this->_form; // Don't forget the underscore!

        // Add first name form field.
        $mform->addElement('text', 'firstname', 'First Name');
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->addRule('firstname', get_string('error:missingfirstname', 'auth_cacpwt'), 'required', null, 'server');

        // Add last name form field.
        $mform->addElement('text', 'lastname', 'Last Name');
        $mform->setType('lastname', PARAM_NOTAGS);
        $mform->addRule('lastname', get_string('error:missinglastname', 'auth_cacpwt'), 'required', null, 'server');

        // Add email address form field.
        $mform->addElement('text', 'email', 'Email Address');
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('error:missingemail', 'auth_cacpwt'), 'required', 'email', 'server');

        // Direct page access -- set up a hash and track it.
        if (!isset($SESSION->cacpwt_registration)) {
            $SESSION->cacpwt_registration = true;

            // Our CAC form session hash.
            $sessionhash = md5(rand(0, 99999999) . time());

            // Store it in two variables. This is needed to deal with error submissions.
            $SESSION->cacpwt_sessionhash = $sessionhash;
            $SESSION->cacpwt_temp_sessionhash = $sessionhash;
        } else {
            // This is a submission.  Get the hash.
            $sessionhash = $SESSION->cacpwt_sessionhash;
        }

        // Set up hash hidden form field.
        $mform->addElement('hidden', 'registerid', $sessionhash);
        $mform->setType('registerid', PARAM_NOTAGS);

        // The submission button(s).
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', 'Register');

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }

    /**
     * Form validation function.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $SESSION;

        $errors = parent::validation($data, $files);

        // Ensure a valid hash is passed and matches the session data.
        if (preg_match('/^[a-f0-9]{32}$/i', $data['registerid'])) {
            if ($SESSION->cacpwt_sessionhash == $data['registerid']) {
                $SESSION->cacpwt_temp_sessionhash = $data['registerid'];
            }
        }

        // Email error checking.
        if (trim($data['email']) == '') {
            $errors['email'] = get_string('error:missingemail', 'auth_cacpwt');
        } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = get_string('error:invalidemail', 'auth_cacpwt');
        }

        return $errors;
    }
}
