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


require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if (!$allowed) {
    return;
}
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);
$restricttouser = get_config('scormengine', 'restrictToUser');

if (!$queries["search"]) {
    if (!$restricttouser) {
        $packages = $DB->get_records('scormengine_package', null, '', '*', $queries["page"] * 10, 10);
        $count = $DB->count_records('scormengine_package');
    } else {

        $packages = $DB->get_records('scormengine_package', ['owner' => $USER->id], '', '*', $queries["page"] * 10, 10);
        $count = $DB->count_records('scormengine_package', ['owner' => $USER->id]);
    }
} else {

    if (!$restricttouser) {
        $select = 'title ' . $DB->sql_regex() . ' :pattern'. ' or '.'description ' . $DB->sql_regex() . ' :pattern2'. ' or '.'filename ' . $DB->sql_regex() . ' :pattern3';
    } else {
        $select = 'owner equals ' .$USER->id. ' AND (title ' . $DB->sql_regex() . ' :pattern'. ' or '.'description '
        . $DB->sql_regex() . ' :pattern2'. ' or '.'filename ' . $DB->sql_regex() . ' :pattern3 )';
    }
    $params = ['pattern' => $queries["search"], 'pattern2' => $queries["search"], 'pattern3' => $queries["search"]];

    $packages = $DB->get_records_select('scormengine_package', $select, $params, '', '*', $queries["page"] * 10, 10);
    $count = $DB->count_records_select('scormengine_package', $select, $params);
}
header('Content-Type: application/json');
echo json_encode(["packages" => $packages, "count" => ($count)], JSON_PRETTY_PRINT);
