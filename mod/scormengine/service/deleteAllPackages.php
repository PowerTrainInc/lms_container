<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if(!$allowed) return;

$queries = array();

$packages = $DB->get_records('scormengine_package',null,'','*');
foreach( $packages as $p)
{
    $res = se_delete('/courses/'.$p->uuid);
    if($res)
    {

        $DB->delete_records('scormengine_package',["uuid"=>$p->uuid]);
    }
}
