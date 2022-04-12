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
 * Task manager for auth method.
 *
 * @package     auth_cacpwt
 * @copyright   2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license     All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'auth_cacpwt\task\data_import',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '01',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
