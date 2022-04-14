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

global $COURSE, $USER, $DB;

$userobject = $DB->get_record('user',  array('id' => $USER->id) );
$roles = get_user_roles(context_system::instance(), $USER->id, false);


$admins = get_admins();
$allowed = false;

foreach ($admins as $admin) {
    if ($admin->id == $USER->id) {
        $allowed = true;
    }
}

foreach ($roles as $role) {
    if ($role->shortname == 'coursecreator') {
        $allowed = true;
    }
}

function getrequestheaders() {
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

$headers = getRequestHeaders();

$bulkkey = get_config('scormengine', 'bulk_api_key');

if (isset($headers["Api-Key"]) == $bulkkey && !is_null($bulkkey)) {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Unauthorized"], JSON_PRETTY_PRINT);
    return;
}
