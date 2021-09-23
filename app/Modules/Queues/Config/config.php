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
];
