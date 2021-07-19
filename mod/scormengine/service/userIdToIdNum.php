<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;

$queries = array();

parse_str($_SERVER['QUERY_STRING'], $queries);


$userObj = $DB->get_record("user", ["id"=> $queries['user_id']]);

header('Content-Type: application/json');
echo json_encode($userObj ,JSON_PRETTY_PRINT);