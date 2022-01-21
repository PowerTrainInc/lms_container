
<?php
global $COURSE, $USER, $DB;

$user_object = $DB->get_record('user',  array('id'=>$USER->id) );
$roles = get_user_roles(context_system::instance(), $USER->id, false);


$admins = get_admins();
$allowed = false;

foreach($admins as $admin) {
   if($admin->id == $USER->id)
        $allowed = true;
}

foreach($roles as $role) {
    if($role->shortname == 'coursecreator')
        $allowed = true;
}

function getRequestHeaders() {
  $headers = array();
  foreach($_SERVER as $key => $value) {
      if (substr($key, 0, 5) <> 'HTTP_') {
          continue;
      }
      $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
      $headers[$header] = $value;
  }
  return $headers;
}

$headers = getRequestHeaders();

$bulkKey = get_config('scormengine', 'bulk_api_key');

if(isset($headers["Api-Key"]) == $bulkKey && !is_null($bulkKey)) $allowed = true;

if(!$allowed)
{
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["error"=>"Unauthorized"],JSON_PRETTY_PRINT);
    return;
}