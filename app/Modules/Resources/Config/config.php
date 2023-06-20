<?php
return [
	/*
	|--------------------------------------------------------------------------
	| Scheduled commands
	| 'command' => 'cron tab'
	|--------------------------------------------------------------------------
	*/
	'schedule' => [
		'emailscheduling' => '*/5 * * * *',
	],

	/*
	|--------------------------------------------------------------------------
	| Admin email address
	|--------------------------------------------------------------------------
	| Specify an email address to send notifications to.
	*/
	'admin_email' => config('mail.from.address'),

	/*
	|--------------------------------------------------------------------------
	| Resource mailing list host
	|--------------------------------------------------------------------------
	| Specify a host name to be used for resource mailing lists. Addresses
	| start with the resource name, suffixed with '-users'. For a resource
	| with a name of "foo", the list would be "foo-users@somehost.org"
	*/
	'email_lists_host' => ltrim(strstr(config('mail.from.address'), '@'), '@'),

	/*
	|--------------------------------------------------------------------------
	| Default access level
	|--------------------------------------------------------------------------
	| Specify a default access level for new resources. Set to '0' for all
	| access levels.
	*/
	'default_access' => 0,
];
