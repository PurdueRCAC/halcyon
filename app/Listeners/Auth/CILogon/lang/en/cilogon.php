<?php
return [
	'listener name' => 'Authentication - CILogon',
	'listener desc' => 'Handles user authentication against CILogon',

	'error' => [
		'profile' => 'Failed to retrieve CILogon profile',
	],
	'sign in' => 'Sign in with CILogon',

	'client id' => 'Client ID',
	'client id desc' => 'Your site\'s CILogon client ID',

	'client secret' => 'Client Secret',
	'client secret desc' => 'Client Secret provided when your organization is registered on CILogon',

	'server' => 'Server',
	'server desc' => 'Typically, you would use the production server https://cilogon.org. However, you can specify a "server" parameter when creating the provider to use the "test" server https://test.cilogon.org or "dev" server https://dev.cilogon.org.',

	'display name' => 'Display Name',
	'display name desc' => 'Text to display on the site when referencing this authenticator',
];
