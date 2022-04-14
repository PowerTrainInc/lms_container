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



try{
require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');

$previousErrorLevel = error_reporting(0);

if (!$allowed) {
    return;
}


$tmpfile = $_FILES["package"]["tmp_name"];
$uploadfilename = basename($_FILES["package"]["name"]);
$uploadok = 1;
$filetype = strtolower(pathinfo($uploadfilename, PATHINFO_EXTENSION));

if ($filetype != "zip") {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Sorry, only ZIP files are allowed."], JSON_PRETTY_PRINT);
    error_reporting($previousErrorLevel);
    return;
}


if ($uploadok == 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Upload not accepted for unknown reason"], JSON_PRETTY_PRINT);
    error_reporting($previousErrorLevel);
    return;

} else {

        $cid = uuid();
        $fullpath = $tmpfile;
        $title = "";


        $upload = se_postFile('/courses/upload?courseId='.$cid.'&dryRun=true', $title, $fullpath);
        error_log(json_encode($upload));
        


    if ($upload) {
        if ( $upload->message == "Authentication Failed" ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Scorm Engine Authentication Failed"], JSON_PRETTY_PRINT);
            error_reporting($previousErrorLevel);
            return;
        }
        if ( $upload->message  ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => $upload->message], JSON_PRETTY_PRINT);
            error_reporting($previousErrorLevel);
            return;
        }
        if ( count($upload->parserWarnings) > 0 ) {

            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Package Error", data => $upload], JSON_PRETTY_PRINT);
            error_reporting($previousErrorLevel);
            return;
        }

        $title = isset($_POST["title"]) ? $_POST["title"] : null;
        $upload = se_postFile('/courses/upload?courseId='.$cid.'&dryRun=false', $title, $fullpath);

        if ( $upload->message  ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => $upload->message], JSON_PRETTY_PRINT);
            error_reporting($previousErrorLevel);
            return;
        }

        $description = isset($_POST["description"]) ? $_POST["description"] : "No Description";
        error_log("----upload----");
        error_log(json_encode($upload));
        error_log("----upload----");
        $newreg = [
            'owner' => $USER->id,
            'title' => $title ??  $upload->course->metadata->title ?? $upload->course->title,
            'description' => $description ??  $upload->course->metadata->description ?? '',
            'filename' => $uploadfilename,
            'uuid' => $cid,
        ];
        header('Content-Type: application/json');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        echo json_encode($newreg, JSON_PRETTY_PRINT);
        $DB->insert_record('scormengine_package', $newreg);
    } else {
        
        http_response_code(500);
        header('Content-Type: application/json');

        echo json_encode(["error" => "Could not connect to Scorm Engine"], JSON_PRETTY_PRINT);
    }

}
}catch(Exception $e)
{
    http_response_code(400);
    error_log($e);
    header('Content-Type: application/json');
    echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
}
error_reporting($previousErrorLevel);