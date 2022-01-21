<?php
/**
 * CSV Downloader
 *
 * @package    local_completion_tracker
 * @copyright  2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

use core\dataformat;

require('../../config.php');
/**
 * @var $CFG
 * @var $DB
 */

redirect_if_major_upgrade_required();

require_login();

require_capability('local/completion_tracker:download', context_system::instance());

// Prepare query
$sql = '
	SELECT cc.id as completion_id,
		u.id as user_id,
		u.idnumber as user_idnumber,
		c.id as course_id,
		c.shortname as course_shortname,
		c.category as course_category,
		IFNULL(ccc.gradepass, \'\') as grade_pass,
		IFNULL((
		    SELECT cfd.value 
		    FROM {customfield_data} cfd, 
		    	{customfield_field} cff 
		    WHERE cfd.fieldid = cff.id 
		    	AND cfd.instanceid = cc.course 
		    	AND cff.shortname = \'course_hours\'
		), \'\') as customfield_course_hours,
		ue.enrolid as enrol_id,
		ue.timestart as time_start,
		IFNULL(cc.timeenrolled, \'\') as time_enrolled,
		IFNULL(cc.timecompleted, \'\') as time_completed,
		IFNULL((
		    SELECT ROUND((gg.finalgrade / gg.rawgrademax) * 100, 4) as rawgrade 
		    FROM {grade_grades} gg, 
		         {grade_items} gi 
		    WHERE gg.itemid = gi.id 
		    	AND gi.courseid = cc.course 
		    	AND gg.userid = cc.userid 
		    	AND gi.itemtype = \'course\'
		), \'\') as raw_grade,
		\'\' as reservepoints
	FROM {enrol} e,
		{user_enrolments} ue,
		{course_completions} cc
	INNER JOIN {user} u ON cc.userid = u.id
	INNER JOIN {course} c ON cc.course = c.id
	LEFT JOIN {course_completion_criteria} ccc ON ccc.course = c.id 
	    AND ccc.criteriatype = \'6\'
	WHERE (ue.enrolid = e.id 
				AND e.courseid = c.id 
				AND ue.userid = u.id 
				AND cc.course = c.id
		) 
		AND cc.timecompleted >= :date_range
';

// Prepare data for output to CSV
$dataformat = optional_param('dataformat', 'csv', PARAM_ALPHA);

$columns = array(
	'completion_id' => get_string('header:completion_id', 'local_completion_tracker'),
	'user_id' => get_string('header:user_id', 'local_completion_tracker'),
	'user_idnumber' => get_string('header:idnumber', 'local_completion_tracker'),
	'course_id' => get_string('header:course_id', 'local_completion_tracker'),
	'course_shortname' => get_string('header:course_shortname', 'local_completion_tracker'),
	'course_category' => get_string('header:course_category', 'local_completion_tracker'),
	'grade_pass' => get_string('header:grade_pass', 'local_completion_tracker'),
	'customfield_course_hours' => get_string('header:customfield_course_hours', 'local_completion_tracker'),
	'enrol_id' => get_string('header:enrol_id', 'local_completion_tracker'),
	'time_start' => get_string('header:time_start', 'local_completion_tracker'),
	'time_enrolled' => get_string('header:time_enrolled', 'local_completion_tracker'),
	'time_completed' => get_string('header:time_completed', 'local_completion_tracker'),
	'raw_grade' => get_string('header:raw_grade', 'local_completion_tracker'),
	'reservepoints' => get_string('header:reservepoints', 'local_completion_tracker')
);

// Date_range is one week ago.
$result = $DB->get_recordset_sql($sql, array('date_range' => (time() - (7 * 60 * 60 * 24))));

// May need to add .csv to the end of the filename
$file_name = 'completion-tracker-' . date('m-d-Y-h-i-s', time());

dataformat::download_data($file_name, $dataformat, $columns, $result);

$result->close();
