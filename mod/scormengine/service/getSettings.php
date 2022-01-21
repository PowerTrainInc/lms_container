<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;

$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

$package = $DB->get_record('scormengine_package', array('uuid' => $queries["id"]), '*', MUST_EXIST);

$link = '/courses/' . $package->uuid . '/configuration?includeMetadata=true&includeHiddenSettings=true&includeSecretSettings=true';
$results = se_get($link);

header('Content-Type: application/json');
echo json_encode($results ,JSON_PRETTY_PRINT);