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
 * PowerTrain CAC Plugin No Access Class
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * CAC No Access Form
 */
class cacpwt_noaccess extends moodleform {
    /**
     * Form definition function.
     */
    public function definition() {
        // Get the style settings from the plugin.
        $noaccess = get_config('auth_cacpwt', 'noaccess');
        $noaccesswidth = get_config('auth_cacpwt', 'noaccess_width');
        $noaccesspadding = get_config('auth_cacpwt', 'noaccess_padding');
        $noaccessbackground = get_config('auth_cacpwt', 'noaccess_background');

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('html', '<div style="text-align: left; width: ' . $noaccesswidth . '; padding: ' .
            $noaccesspadding . '; background-color: ' . $noaccessbackground . ';">' . $noaccess . '</div>');

    }
}
