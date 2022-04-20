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
 * Contains the class that provides a grade object to be used by elements for display purposes.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

namespace mod_customcertpwt;

defined('MOODLE_INTERNAL') || die();

/**
 * The class that provides a grade object to be used by elements for display purposes.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class grade_information {

    /**
     * @var string The grade name.
     */
    protected $name;

    /**
     * @var float The raw grade.
     */
    protected $grade;

    /**
     * @var string The grade to display
     */
    protected $displaygrade;

    /**
     * @var int The date it was graded.
     */
    protected $dategraded;

    /**
     * The constructor.
     *
     * @param string $name
     * @param float $grade
     * @param string $displaygrade
     * @param int $dategraded
     */
    public function __construct($name, $grade, $displaygrade, $dategraded) {
        $this->name = $name;
        $this->grade = $grade;
        $this->displaygrade = $displaygrade;
        $this->dategraded = $dategraded;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Returns the raw grade.
     *
     * @return float
     */
    public function get_grade() {
        return $this->grade;
    }

    /**
     * Returns the display grade.
     *
     * @return string
     */
    public function get_displaygrade() {
        return $this->displaygrade;
    }

    /**
     * Returns the date it was graded.
     *
     * @return int
     */
    public function get_dategraded() {
        return $this->dategraded;
    }
}
