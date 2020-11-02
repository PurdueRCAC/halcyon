<?php
return [
	'listener name' => 'CILogon',
	'listener desc' => 'Handles user authentication against CILogon',

	// Misc.
	'must authorize to login' => 'To log in via CILogon, you must authorize the :sitename app.',
	'must authorize to link' => 'To link the current account with your CILogon account, you must authorize the %s app.',
	'error retrieving profile' => 'Failed to retrieve CILogon profile (%s).',
	'unknown user' => 'Unknown user and new user registration is not permitted.',
	'authentication failed' => 'Username and password do not match or you do not have an account yet.',

	// Params
	'key' => 'Client ID',
	'key desc' => 'Your site\'s CILogon App ID',
	'secret' => 'Client Secret',
	'secret desc' => 'Client Secret provided when your hub is registered on CILogon',
	'name' => 'Display name',
	'name desc' => 'Text to display on the site when referencing this plugin',
	'site login' => 'Site login',
	'site login desc' => 'Enable this plugin for frontend authentication',
	'admin login' => 'Admin login',
	'admin login desc' => 'Enable this plugin for backend authentication',
];