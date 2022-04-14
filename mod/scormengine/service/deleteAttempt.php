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

$res = se_delete('/registrations/'.$queries['rid']);
if ($res) {

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([], JSON_PRETTY_PRINT);
    $DB->delete_records("scormengine_registration", ["registration" => $queries['rid']]);

} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([error => ""], JSON_PRETTY_PRINT);
    $DB->delete_records("scormengine_registration", ["registration" => $queries['rid']]);
}
