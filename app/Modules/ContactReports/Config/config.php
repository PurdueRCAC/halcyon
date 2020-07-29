<?php

return [
	'name' => 'Contact Reports',

	/*
	|--------------------------------------------------------------------------
	| Scheduled commands
	| 'command' => 'cron tab'
	|--------------------------------------------------------------------------
	*/
	'schedule' => [
		'emailreports' => '*/10 * * * *',
		'emailcomments' => '*/10 * * * *',
	]
];
