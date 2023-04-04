<?php
return [
	'listener name' => 'Authentication - CAS',
	'listener desc' => 'Handles user authentication against CAS',

	'error' => [
		'expired ticket' => 'CAS ticket has expired',
		'unknown user' => 'Unknown user and new user registration is not permitted.',
		'authentication failed' => 'Username and password do not match or you do not have an account yet.',
	],

	'sign in' => 'Sign in with :name',
];
