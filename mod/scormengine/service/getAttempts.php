<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;


$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

$registrations = $DB->get_records('scormengine_registration',['package_id'=>$queries['cid']],'','*',$queries["page"]*10,10);
$count = $DB->count_records('scormengine_registration',['package_id'=>$queries['cid']]);


foreach($registrations as $reg) {
    $user_object = $DB->get_record('user',  array('id'=>$reg->user_id) );
    $reg->user=["email"=>$user_object->email,"username"=>$user_object->username];
 }



header('Content-Type: application/json');
echo json_encode(["count"=>$count,"registrations"=>$registrations],JSON_PRETTY_PRINT);