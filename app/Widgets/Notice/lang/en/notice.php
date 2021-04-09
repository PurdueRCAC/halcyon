<?php

return [
	'widget name' => 'Site Notices',
	'widget desc' => 'This module shows a notice (when site will be down, etc.) box for site visitors.',
	// Misc.
	'in' => 'in',
	'years' => 'years',
	'months' => 'months',
	'days' => 'days',
	'hours' => 'hours',
	'minutes' => 'minutes',
	'seconds' => 'seconds',
	'immediately' => 'starting immediately',
	'close' => 'close',
	'close title' => 'Close this notice',
	// Parameters
	'alertlevel' => 'Alert level',
	'alertlevel desc' => 'The alert level the notice field will take. Determines color.',
	'alertlevels' => [
		'low' => 'Low',
		'medium' => 'Medium',
		'high' => 'High',
	],
	'htmlid' => 'Widget ID',
	'htmlid desc' => 'An ID to be applied to the css of the widget container, this allows individual widget styling',
	'message' => 'Message',
	'message desc' => 'The message to be displayed.',
	'allow closing' => 'Allow closing',
	'allow closing desc' => 'Allow the notice to be closed by the user',
	'auto link' => 'Autolink message',
	'auto link desc' => 'Autolink urls &amp; email addresses in notice content.',
];
