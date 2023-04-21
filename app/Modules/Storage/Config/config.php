<?php

return [
	'name' => 'Storage',

	/*
	|--------------------------------------------------------------------------
	| IDs of storage resources that have lettered spaces
	| Note: This should be moved to a database setting
	|--------------------------------------------------------------------------
	*/
	'alphabetical' => [],

	/*
	|--------------------------------------------------------------------------
	| Scheduled commands
	| 'command' => 'cron tab'
	|--------------------------------------------------------------------------
	*/
	'schedule' => [
		// Every half hour
		'emailquota' => '*/30 * * * *',

		// Every day at 4:00am
		'quotaupdate' => '0 4 * * *',

		// Every half hour
		'quotacheck' => '*/30 * * * *',
	],
];
