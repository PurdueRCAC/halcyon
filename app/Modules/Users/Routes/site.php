<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('login', [
	'as'   => 'login',
	'uses' => 'AuthController@login'
]);
$router->post('login', [
	'as'   => 'login.post',
	'uses' => 'AuthController@authenticate'
]);
$router->get('callback', [
	'as'   => 'callback',
	'uses' => 'AuthController@authenticate'
]);
$router->get('logout', [
	'as'   => 'logout',
	'uses' => 'AuthController@logout'
]);

if (config('module.users.allow_registration', true))
{
	$router->get('register', [
		'as'   => 'register',
		'uses' => 'RegisterController@index'
	]);
	$router->post('register', [
		'as'   => 'register.post',
		'uses' => 'RegisterController@store'
	]);

	// Account Activation
	//verify-email/{id}/{hash}
	$router->get(
		'activate/{id}/{code}',
		'AuthController@activate'
	);
}

// Remind password
$router->get('forgot-password', [
	'as' => 'password.forgot',
	'uses' => 'ForgotPasswordController@index'
]);
$router->post('forgot-password', [
	'as' => 'password.email',
	'uses' => 'ForgotPasswordController@store'
]);

// Reset password
$router->get('reset-password', [
	'as' => 'password.reset',
	'uses' => 'ResetPasswordController@index'
]);
$router->post('reset-password', [
	'as' => 'password.update',
	'uses' => 'ResetPasswordController@store'
]);

$router->group(['prefix' => 'account', 'middleware' => 'auth'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.users.account',
		'uses' => 'UsersController@account',
	]);
	$router->get('myinfo', [
		'uses' => 'UsersController@account',
	]);
	/*$router->get('request', [
		'as' => 'site.users.account.request',
		'uses' => 'UsersController@request',
	]);*/
	if (config('module.users.allow_self_deletion'))
	{
		$router->get('delete', [
			'as' => 'site.users.account.delete',
			'uses' => 'UsersController@delete',
		]);
	}
	$router->get('{section}', [
		'as' => 'site.users.account.section',
		'uses' => 'UsersController@account',
	])->where('section', '[a-zA-Z0-9\-_]+');
	$router->get('{section}/{id}', [
		'as' => 'site.users.account.section.show',
		'uses' => 'UsersController@account',
	])->where('section', '[a-zA-Z0-9]+')->where('id', '[0-9]+');
	$router->get('{section}/{id}/{subsection}', [
		'as' => 'site.users.account.section.show.subsection',
		'uses' => 'UsersController@account',
	])->where('section', '[a-zA-Z0-9]+')->where('id', '[0-9]+')->where('subsection', '[a-zA-Z0-9]+');
});

// Impersonation
$router->get('impersonate/take/{id}/{guardName?}', [
	'as' => 'impersonate',
	'uses' => '\Lab404\Impersonate\Controllers\ImpersonateController@take',
	'middleware' => ['auth', 'can:manage users']
]);
$router->get('impersonate/leave', [
	'as' => 'impersonate.leave',
	'uses' => '\Lab404\Impersonate\Controllers\ImpersonateController@leave',
	'middleware' => ['auth']
]);
