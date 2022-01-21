<?php
/**
 *
 * @package    tool_structureimport
 * @copyright  PowerTrain Inc
 * @license    All Rights Reserved
 */

namespace tool_structureimport\event;

defined('MOODLE_INTERNAL') || die();

/**
 * This event is triggered when a certificate template is updated.
 *
 * @package tool_structureimport
 */
class certificate_template_updated extends \core\event\base {
    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'customcertpwt';
        $this->data['crud'] = 'u';
		$this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localized general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('log:certificatetemplateupdated', 'tool_structureimport');
    }

    /**
     * Returns non-localized event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
		global $USER;
		
		return get_string('log:certificatetemplateupdateddescription', 'tool_structureimport', array('userid' => $USER->id));
    }
}

