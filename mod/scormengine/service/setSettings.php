<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;

$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

$package = $DB->get_record('scormengine_package', array('uuid' => $queries["id"]), '*', MUST_EXIST);

// Takes raw data from the request
$json = file_get_contents('php://input');

// Converts it into a PHP object
$data = json_decode($json);

$link = '/courses/' . $package->uuid . '/configuration';
$results = se_postJSON($link,$data);

header('Content-Type: application/json');
echo json_encode($data ,JSON_PRETTY_PRINT);