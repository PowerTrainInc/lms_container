<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;

$queries = array();

parse_str($_SERVER['QUERY_STRING'], $queries);

$res = se_delete('/registrations/'.$queries['rid']);
if($res)
{

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([],JSON_PRETTY_PRINT);
    $DB->delete_records("scormengine_registration",["registration"=>$queries['rid']]);

}else{
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([error=>""],JSON_PRETTY_PRINT);
    $DB->delete_records("scormengine_registration",["registration"=>$queries['rid']]);
}