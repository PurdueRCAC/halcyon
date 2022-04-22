<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Allow user registration?
	|--------------------------------------------------------------------------
	|
	| When set to `false`, the registration form will not be available.
	|
	*/
	'allow_registration' => false,

	/*
	|--------------------------------------------------------------------------
	| Allow self deletion
	|--------------------------------------------------------------------------
	|
	| Allow users to delete their accounts.
	|
	*/
	'allow_self_deletion' => false,

	/*
	|--------------------------------------------------------------------------
	| Profile Photos
	|--------------------------------------------------------------------------
	|
	| Display profile photos and allow users to change them
	|
	*/
	'profile_photos' => false,

	/*
	|--------------------------------------------------------------------------
	| Terms of Service
	|--------------------------------------------------------------------------
	|
	| Set the ID of a page in the Pages module to act as the Terms of Service
	|
	*/
	'terms' => 0,

	/*
	|--------------------------------------------------------------------------
	| Redirect route after login
	|--------------------------------------------------------------------------
	|
	| Where to redirect to after login.
	|
	*/
	'redirect_route_after_login' => 'site.users.account',

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
