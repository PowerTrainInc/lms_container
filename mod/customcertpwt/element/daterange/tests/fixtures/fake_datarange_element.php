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
 * Fake datarange element for testing.
 *
 * @package    customcertpwt_element_daterange
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Fake datarange element for testing.
 *
 * @package    customcertpwt_element_daterange
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class fake_datarange_element extends \customcertpwt_element_daterange\element {

    /**
     * Override protected method for testing.
     *
     * @param int $date
     *
     * @return string
     */
    public function get_daterange_string($date) {
        $result = parent::get_daterange_string($date);
        return $result;
    }
}
