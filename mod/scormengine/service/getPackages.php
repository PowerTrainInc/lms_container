<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);
$restrictToUser = get_config('scormengine', 'restrictToUser');

if(!$queries["search"])
{
    if(!$restrictToUser)
    {
        $packages = $DB->get_records('scormengine_package',null,'','*',$queries["page"]*10,10);
        $count = $DB->count_records('scormengine_package');
    }
    else{

        $packages = $DB->get_records('scormengine_package',['owner'=>$USER->id],'','*',$queries["page"]*10,10);
        $count = $DB->count_records('scormengine_package',['owner'=>$USER->id]);
    }
}else{

    if(!$restrictToUser)
        $select = 'title ' . $DB->sql_regex() . ' :pattern'. ' or '.'description ' . $DB->sql_regex() . ' :pattern2'. ' or '.'filename ' . $DB->sql_regex() . ' :pattern3';
    else
        $select = 'owner equals ' .$USER->id. ' AND (title ' . $DB->sql_regex() . ' :pattern'. ' or '.'description ' . $DB->sql_regex() . ' :pattern2'. ' or '.'filename ' . $DB->sql_regex() . ' :pattern3 )';
    $params = ['pattern' => $queries["search"],'pattern2' => $queries["search"],'pattern3' => $queries["search"]];

    $packages = $DB->get_records_select('scormengine_package',$select,$params,'','*',$queries["page"]*10,10);
    $count = $DB->count_records_select('scormengine_package',$select,$params);
}
header('Content-Type: application/json');
echo json_encode(["packages"=>$packages,"count"=>($count)],JSON_PRETTY_PRINT);