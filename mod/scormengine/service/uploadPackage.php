<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');

if(!$allowed) return;
//console_log($_FILES);
//console_log( $_FILES["package"]["tmp_name"]);
//console_log( "$tmp_file" );
//console_log( "fileType" );
//console_log( "$fileType" );

// Allow certain file formats

$tmp_file = $_FILES["package"]["tmp_name"];
$upload_filename = basename($_FILES["package"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($upload_filename,PATHINFO_EXTENSION));

if($fileType != "zip") {
  header('Content-Type: application/json');
  echo json_encode(["error"=>"Sorry, only ZIP files are allowed."],JSON_PRETTY_PRINT);
  return;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  header('Content-Type: application/json');
  echo json_encode(["error"=>"Upload not accepted for unknown reason"],JSON_PRETTY_PRINT);
  return;
// if everything is ok, try to upload file
} else {
  
        $cid = uuid();
        $fullpath = $tmp_file;
        $title = "";
      
        //console_log("Save File");
        //console_log($fullpath);
        //console_log($success);
        $upload = se_postFile('/courses/upload?courseId='.$cid.'&dryRun=true',$title,$fullpath);
        //console_log($upload);
        
        
        if($upload)
        {
          if( $upload->message == "Authentication Failed" )
          {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error"=>"Scorm Engine Authentication Failed"],JSON_PRETTY_PRINT);
            return;
          }
          if( count($upload->parserWarnings) > 0 )
          {

            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error"=>"Package Error", data=>$upload],JSON_PRETTY_PRINT);
            return;
          }
  
          $title = isset($_POST["title"]) ? $_POST["title"] : null;
          $upload = se_postFile('/courses/upload?courseId='.$cid.'&dryRun=false',$title,$fullpath);
          $description = isset($_POST["description"]) ? $_POST["description"] : null;
            $newReg = [
                'owner'=>$USER->id,
                'title'=>$title ?? ( $upload->course->metadata->title ?? $upload->course->title),
                'description'=>$description ?? ( $upload->course->metadata->description ?? ''),
                'filename'=>$upload_filename,
                'uuid'=>$cid,
            ];
            header('Content-Type: application/json');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            echo json_encode($newReg,JSON_PRETTY_PRINT);
            $DB->insert_record('scormengine_package', $newReg);
        } else 
        {
            error_log("Could not connect to Scorm Engine");
            http_response_code(500);
            header('Content-Type: application/json');
           // header('Location: ' . $_SERVER['HTTP_REFERER']);
            echo json_encode(["error"=>"Could not connect to Scorm Engine"],JSON_PRETTY_PRINT);
        }
    
}