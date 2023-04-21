<?php

return [
	'name' => 'Queues',

	/*
	|--------------------------------------------------------------------------
	| Scheduled commands
	| 'command' => 'cron tab'
	|--------------------------------------------------------------------------
	*/
	'schedule' => [
		'emailqueueauthorized' => '*/10 * * * *',
		'emailfreeauthorized' => '*/20 * * * *',

		'emailqueuedenied' => '*/20 * * * *',
		'emailfreedenied' => '*/20 * * * *',

		'emailqueueremoved' => '*/10 * * * *',
		'emailfreeremoved' => '*/20 * * * *',

		'emailqueuerequested' => '*/20 * * * *',
		'emailfreerequested' => '*/20 * * * *',

		'emailwelcomecluster' => '0 5 * * * ',
		'emailwelcomefree' => '0 5 * * * ',
	],

	/*
	|--------------------------------------------------------------------------
	| Prefix for auto-generated queues for new subresources
	|--------------------------------------------------------------------------
	*/
	'prefix' => 'system-',

	/*
	|--------------------------------------------------------------------------
	| Default values for auto-generated queues
	|--------------------------------------------------------------------------
	*/
	'maxjobsqueued' => 12000,
	'maxjobsqueueduser' => 5000,

	/*
	|--------------------------------------------------------------------------
	| Stop all queues command
	|--------------------------------------------------------------------------
	*/
	'stop_all_cmd' => 'sudo -u apache -- ssh $HOST qpanic -d',

	/*
	|--------------------------------------------------------------------------
	| Start all queues command
	|--------------------------------------------------------------------------
	*/
	'start_all_cmd' => 'sudo -u apache -- ssh $HOST qpanic -u',

	/*
	|--------------------------------------------------------------------------
	| User role to automatically add users to system and admin queues
	|--------------------------------------------------------------------------
	*/
	'admins' => [],
];
