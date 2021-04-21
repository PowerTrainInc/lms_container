<?php

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/seo_xapi.php');

$settings = get_config('scormengine');
$registration_id = optional_param('rid', 0, PARAM_TEXT);
$course_link = optional_param('courselink', 0, PARAM_TEXT);

$link = '/registrations/' . $registration_id . '?includeRuntime=true&includeInteractionsAndObjectives=true&includeChildResults=true';
$results = se_get($link);

send_xapi_statements_from_seo('INITIALIZE', $results, $settings->lrs_endpoint, $settings->lrs_username, $settings->lrs_password);

redirect($course_link);

?>
