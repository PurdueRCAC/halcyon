<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'classaccount'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.courses.index',
		'uses' => 'AccountsController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'api.courses.create',
		'uses' => 'AccountsController@create',
		'middleware' => ['auth:api', 'can:create courses'],
	]);
	$router->get('{id}', [
		'as' => 'api.courses.read',
		'uses' => 'AccountsController@read',
		'middleware' => 'auth:api',
	]);
	$router->put('{id}', [
		'as' => 'api.courses.update',
		'uses' => 'AccountsController@update',
		'middleware' => 'auth:api|can:edit courses',
	]);
	$router->delete('{id}', [
		'as' => 'api.courses.delete',
		'uses' => 'AccountsController@delete',
		'middleware' => 'auth:api|can:delete courses',
	]);
});

$router->get('classsync', [
	'as' => 'api.courses.sync',
	'uses' => 'AccountsController@sync',
	'middleware' => 'auth:api',
]);

$router->group(['prefix' => 'classuser'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.courses.members',
		'uses' => 'MembersController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'api.courses.members.create',
		'uses' => 'MembersController@create',
		'middleware' => ['auth:api', 'can:edit courses,edit.own courses'],
	]);
	$router->get('{id}', [
		'as' => 'api.courses.members.read',
		'uses' => 'MembersController@read',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.courses.members.update',
		'uses' => 'MembersController@update',
		'middleware' => ['auth:api', 'can:edit courses,edit.own courses'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.courses.members.delete',
		'uses' => 'MembersController@delete',
		'middleware' => ['auth:api', 'can:edit courses,edit.own courses'],
	])->where('id', '[0-9]+');
});
