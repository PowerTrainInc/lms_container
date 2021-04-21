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

/**
 * Library of interface functions and constants.
 *
 * @package     mod_scormengine
 * @copyright   Veracity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function scormengine_supports($feature) {
    switch ($feature) {
        case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_GRADE_HAS_GRADE: return true;
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

function scormengine_grade_item_update($modinstance, $grades=NULL)
{
    

}

function scormengine_update_grades($modinstance, $userid=0, $nullifnone=true)
{
}
/**
 * Saves a new instance of the mod_scormengine into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_scormengine_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function scormengine_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('scormengine', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_scormengine in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_scormengine_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function scormengine_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('scormengine', $moduleinstance);
}

/**
 * Removes an instance of the mod_scormengine from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function scormengine_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('scormengine', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('scormengine', array('id' => $id));

    return true;
}




function se_get($url)
{
    $settings = get_config('scormengine');
    $ch = curl_init($url);
    console_log("GET ".$settings->endpoint."/RusticiEngine/api/v2".$url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $settings->endpoint."/RusticiEngine/api/v2".$url);

    $verbose = fopen('php://temp', 'rw+');

    curl_setopt($ch, CURLOPT_STDERR , $verbose);
    curl_setopt($ch, CURLOPT_VERBOSE  , TRUE);

    $headers = array(
        'Authorization: Basic '. base64_encode($settings->username.':'.$settings->password),
        'engineTenantName: default'
    );
    console_log($headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $result = curl_exec($ch);
    !rewind($verbose);
    curl_close($ch);
    console_log('CURL result:');
    console_log($result);
    console_log(htmlspecialchars(stream_get_contents($verbose)));

    $obj = json_decode($result);
    return $obj;
}

function uuid(){
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); 
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function se_postJSON($url,$body)
{
    $data_string = json_encode($body);
    $settings = get_config('scormengine');
    $ch = curl_init(url);
    console_log("POST ".$settings->endpoint."/RusticiEngine/api/v2".$url);
    console_log($body);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $settings->endpoint."/RusticiEngine/api/v2".$url);

    $verbose = fopen('php://temp', 'rw+');

    curl_setopt($ch, CURLOPT_STDERR , $verbose);
    curl_setopt($ch, CURLOPT_VERBOSE  , TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string );

    $headers = array(
        'Authorization: Basic '. base64_encode($settings->username.':'.$settings->password),
        'engineTenantName: default',
        'Content-Type: application/json'
    );
    console_log($headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $result = curl_exec($ch);
    !rewind($verbose);
    curl_close($ch);
    console_log('CURL result:');
    console_log($result);
    console_log(htmlspecialchars(stream_get_contents($verbose)));

    $obj = json_decode($result);
    return $obj;
}

function se_postFile($url,$filename,$filepath)
{
   
    $settings = get_config('scormengine');
    $ch = curl_init(url);
    console_log("POST ".$settings->endpoint."/RusticiEngine/api/v2".$url);
    console_log($body);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $settings->endpoint."/RusticiEngine/api/v2".$url);

    $verbose = fopen('php://temp', 'rw+');

    curl_setopt($ch, CURLOPT_STDERR , $verbose);
    curl_setopt($ch, CURLOPT_VERBOSE  , TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);

    $metadata = array(
        title=>  $filename,
        titleLanguage=> 'string',
        description=> 'string',
        descriptionLanguage=> 'string',
        duration=> 'string',
        typicalTime=> 'string',
        keywords=> json_decode('{}'),
        pluginSpecificMetadata=> json_decode('{}')
    );
    $formData = [
        // Pass a simple key-value pair
        contentMetadata=> json_encode($metadata),
        // Pass data via Buffers
        // Pass data via Streams
        file=> new CurlFile($filepath, 'application/zip', $filename)

    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, $formData );

    $headers = array(
        'Authorization: Basic '. base64_encode($settings->username.':'.$settings->password),
        'engineTenantName: default',
        'Content-Type: multipart/form-data'
    );
    console_log($headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $result = curl_exec($ch);
    !rewind($verbose);
    curl_close($ch);
    console_log('CURL result:');
    console_log($result);
    console_log(htmlspecialchars(stream_get_contents($verbose)));

    $obj = json_decode($result);
    return $obj;
}

function console_log($output, $with_script_tags = true) {
    return;
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}


function scormengine_get_completion_state($course,$cm,$userid,$type) {
    global $CFG,$DB;
    console_log("scormengine_get_completion_state");
    console_log($course->id);
    console_log($cm->id);
    console_log($userid);
    $moduleinstance = $DB->get_record('scormengine', array('id' => $cm->instance), '*', MUST_EXIST);
    $existingReg = $DB->get_record('scormengine_registration', array('course_id' => $course->id, 'mod_id' => $moduleinstance->id, "user_id" => $userid, 'package_id' => $moduleinstance->package_id ), '*', IGNORE_MISSING);
    console_log($existingReg);
    if($existingReg->completion == 1)
        return true;
    return false;
}