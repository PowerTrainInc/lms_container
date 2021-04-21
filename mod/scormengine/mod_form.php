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
 * The main mod_scormengine configuration form.
 *
 * @package     mod_scormengine
 * @copyright   Veracity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();




function name($i)
{
    return $i->title;
};

require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_scormengine
 * @copyright  Veracity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_scormengine_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    
    
    public function definition() {
        console_log('got here');
        global $CFG;

        $settings = get_config('scormengine');

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('scormenginename', 'mod_scormengine'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'scormenginename', 'mod_scormengine');
        $mform->addElement('header', 'Module Choice', get_string('scormenginefieldset', 'mod_scormengine'));
        $mform->addElement('filepicker', 'packageFile', "Scorm Package", null,
                   array('maxbytes' => 100000000, 'accepted_types' => 'zip'));

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_scormengine settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
       
        
        $courses = se_get('/courses');
        $all = $courses->courses;
        while($courses && $courses->more)
        {
            $courses = se_get('/courses?more='.$courses->more);
            $all=array_merge($all,$courses->courses);
        }

        $ynoptions = array();

        foreach ($all as $key => $value)
        {
            $ynoptions[$value->id] = $value->title;
        }

        $mform->addElement('select', 'package_id', 'Choose Existing Package', $ynoptions);


        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        
        $mform = $this;
        $name = $mform->get_new_filename('packageFile');
        console_log("mod form");
        console_log($data);
        console_log($name);
        if($name)
        {
            $cid = uuid();
            $fullpath = sys_get_temp_dir()."/".$name;
            $success = $mform->save_file('packageFile', $fullpath, true);
            console_log("Save File");
            console_log($fullpath);
            console_log($success);
            $upload = se_postFile('/courses/upload?courseId='.$cid.'&dryRun=false',$name,$fullpath);
            $data->package_id = $cid;
        }
        console_log('ddata is now ');
        console_log($data);
        return $data;
    }
    public function completion_rule_enabled($data) {
        return true;
    }
    public function add_completion_rules() {

        $mform = $this->_form;
    
        $mform->addElement('checkbox', 'se_completion', ' ', 'Use Scorm Engine completion');
        $mform->setDefault('se_completion', true);
     
        return ['se_completion'];
    }
}
