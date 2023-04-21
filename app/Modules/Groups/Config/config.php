<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Scheduled commands
	| 'command' => 'cron tab'
	|--------------------------------------------------------------------------
	*/
	'schedule' => [
		'emailremoved' => '*/20 * * * *',
		'emailauthorized' => '*/20 * * * *',
	],

	/*
	|--------------------------------------------------------------------------
	| Prefix for institution group names
	|--------------------------------------------------------------------------
	| This is used in cases where the group information is registered with a
	| central institution services. May not apply to all institutions.
	*/
	'unix_group_prefix' => 'sys-',
];
