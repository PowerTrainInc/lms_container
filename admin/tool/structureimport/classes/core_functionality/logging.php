<?php
/**
 * This file contains the structureimport core logging functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\core_functionality;

require_once($CFG->libdir . '/completionlib.php');

defined('MOODLE_INTERNAL') || die();

class logging {
	/**
     * Constructor.
     */
    public function __construct() {
	
	}

    /**
     * Logs structureimport activity.
     *
     * @param	string objectid
	 * @param	string userid
	 * @param	string relateduserid
	 * @param	string class name
     */
	public function create($objectid, $userid, $relateduserid, $class_name) {
		$eventdata = array('context' => \context_system::instance());
		$eventdata['objectid'] = $objectid;
		$eventdata['userid'] = $userid;
		$eventdata['relateduserid'] = $relateduserid;

		$class = '\\tool_structureimport\\event\\' . $class_name;

		$event = $class::create($eventdata);
		$event->trigger();
								
		unset($eventdata, $event, $class);
	}

}