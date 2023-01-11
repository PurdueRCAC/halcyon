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

		// Every day right before midnight
		'quotaupdate' => '59 23 * * *',
	],
];
