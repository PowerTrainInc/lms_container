<?php
/**
 * @package		local_completion_tracker
 * @copyright	2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license		All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
	'local/completion_tracker:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'student' => CAP_PREVENT
		)
	),
	'local/completion_tracker:archive' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'student' => CAP_PREVENT
		)
	),	'local/completion_tracker:download' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'student' => CAP_PREVENT
		)
	)
);
