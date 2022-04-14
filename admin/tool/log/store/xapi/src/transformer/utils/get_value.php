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

namespace src\transformer\utils;

defined('MOODLE_INTERNAL') || die();

function get_value($data, $key, $default = null) {
    $value = $default;
    if (is_array($data) && array_key_exists($key, $data)) {
        $value = $data[$key];
    } elseif (is_object($data) && property_exists($data, $key)) {
        $value = $data->$key;
    } else {
        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (is_array($data) && array_key_exists($segment, $data)) {
                $value = $data = $data[$segment];
            } elseif (is_object($data) && property_exists($data, $segment)) {
                $value = $data = $data->$segment;
            } else {
                $value = $default;
                break;
            }
        }
    }
    return $value;
}
