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
		'emailqueuedenied' => '*/20 * * * *',
		'emailqueueauthorized' => '*/10 * * * *',
		'emailqueueremoved' => '*/10 * * * *',
		'emailqueuerequested' => '*/20 * * * *',
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
