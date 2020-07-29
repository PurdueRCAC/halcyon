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
	]
];
