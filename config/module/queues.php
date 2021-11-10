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
	'prefix' => 'rcac-',

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
	//'stop_all_cmd' => 'sudo -u rcacdata -- ssh $HOST qpanic -d',
	'stop_all_cmd' => 'sudo -u rcacdata -- ssh $HOST hostname',

	/*
	|--------------------------------------------------------------------------
	| Start all queues command
	|--------------------------------------------------------------------------
	*/
	//'start_all_cmd' => 'sudo -u rcacdata -- ssh $HOST qpanic -u',
	'start_all_cmd' => 'sudo -u rcacdata -- ssh $HOST hostname',
];