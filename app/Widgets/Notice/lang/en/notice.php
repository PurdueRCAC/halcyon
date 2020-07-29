<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

return [
	'widget name' => 'Site Notices',
	'widget desc' => 'This module shows a notice (when site will be down, etc.) box for site visitors.',
	// Misc.
	'in' => 'in',
	'YEARS' => 'years',
	'MONTHS' => 'months',
	'DAYS' => 'days',
	'HOURS' => 'hours',
	'MINUTES' => 'minutes',
	'SECONDS' => 'seconds',
	'IMMEDIATELY' => 'starting immediately',
	'CLOSE' => 'close',
	'CLOSE_TITLE' => 'Close this notice',
	// Parameters
	'PARAM_ALERTLEVEL_LABEL' => 'Alert level',
	'PARAM_ALERTLEVEL_DESC' => 'The alert level the notice field will take. Determines color.',
	'PARAM_ALERTLEVEL_LOW' => 'Low',
	'PARAM_ALERTLEVEL_MEDIUM' => 'Medium',
	'PARAM_ALERTLEVEL_HIGH' => 'High',
	'PARAM_MODULEID_LABEL' => 'Module ID',
	'PARAM_MODULEID_DESC' => 'An ID to be applied to the css of the module container, this allows individual module styling',
	'PARAM_MESSAGE_LABEL' => 'Message',
	'PARAM_MESSAGE_DESC' => 'The message to be displayed.',
	'PARAM_ALLOWCLOSING_LABEL' => 'Allow closing',
	'PARAM_ALLOWCLOSING_DESC' => 'Allow the notice to be closed by the user',
	'PARAM_AUTOLINK_LABEL' => 'Autolink message',
	'PARAM_AUTOLINK_DESC' => 'Autolink urls &amp; email addresses in notice content.',
];
