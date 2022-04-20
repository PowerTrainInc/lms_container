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
 * Contains the course module viewed event class.
 *
 * @package   mod_customcertpwt
 * @copyright 2021 PowerTrain Inc
 * @license   All Rights Reserved
 */

namespace mod_customcertpwt\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The course module viewed event class.
 *
 * @package   mod_customcertpwt
 * @copyright 2021 PowerTrain Inc
 * @license   All Rights Reserved
 */
class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Initialises the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'customcertpwt';
        parent::init();
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public static function get_objectid_mapping() {
        return array('db' => 'customcertpwt', 'restore' => 'customcertpwt');
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public static function get_other_mapping() {
        // No need to map.
        return false;
    }
}
