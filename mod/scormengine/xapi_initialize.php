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


require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/seo_xapi.php');

$settings = get_config('scormengine');
$registrationid = optional_param('rid', 0, PARAM_TEXT);
$courselink = optional_param('courselink', 0, PARAM_TEXT);

$link = '/registrations/' . $registrationid . '?includeRuntime=true&includeInteractionsAndObjectives=true&includeChildResults=true';
$results = se_get($link);

send_xapi_statements_from_seo('INITIALIZE', $results, $settings->lrs_endpoint, $settings->lrs_username, $settings->lrs_password);

redirect($courselink);

