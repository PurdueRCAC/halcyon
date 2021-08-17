<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Module Name
	|--------------------------------------------------------------------------
	|
	| The name of this module.
	|
	*/
	'name' => 'Users',

	/*
	|--------------------------------------------------------------------------
	| Profile options
	|--------------------------------------------------------------------------
	|
	| Profile fields. Currently not used.
	|
	*/
	'profiles' => [
		'org' => [
			'type' => 'textarea',
			'label' => '',
			'desc' => '',
			'required' => false,
		],
		'orcid' => [
			'type' => 'text',
			'label' => '',
			'desc' => '',
			'required' => false,
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Redirect route after login
	|--------------------------------------------------------------------------
	|
	| Where to redirect to after login.
	|
	*/
	'redirect_route_after_login' => 'home',

	/*
	|--------------------------------------------------------------------------
	| Redirect route after logout
	|--------------------------------------------------------------------------
	|
	| Where to redirect to after logout. Default is back to the home page.
	|
	*/
	'redirect_route_after_logout' => 'home',

	/*
	|--------------------------------------------------------------------------
	| New User role
	|--------------------------------------------------------------------------
	|
	| The user role to apply to newly created accounts.
	|
	*/
	'new_usertype' => 2,

	/*
	|--------------------------------------------------------------------------
	| Create on login
	|--------------------------------------------------------------------------
	|
	| Auto-create accounts upon login? This only applies to cases where
	| authentication is done through a 3rd-party service.
	|
	*/
	'create_on_login' => 1,

	/*
	|--------------------------------------------------------------------------
	| Restore on login
	|--------------------------------------------------------------------------
	|
	| Restore "trashed" accounts on login?
	|
	*/
	'restore_on_login' => 0,

	/*
	|--------------------------------------------------------------------------
	| Create on search selection
	|--------------------------------------------------------------------------
	|
	| Auto-create accounts upon selecting one from a search result. This
	| only applies to augmented search results with data from 3rd-party sources
	| such as LDAP. If enabled, this will auto-create a portal account for
	| anyone search result entry that doesn't have one.
	|
	*/
	'create_on_search' => 0,
];
