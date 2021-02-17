<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'classaccount'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.courses.index',
		'uses' => 'AccountsController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'ws.courses.create',
		'uses' => 'AccountsController@create',
		'middleware' => ['auth:api', 'can:create courses'],
	]);
	$router->get('{id}', [
		'as' => 'ws.courses.read',
		'uses' => 'AccountsController@read',
		'middleware' => 'auth:api',
	]);
	$router->put('{id}', [
		'as' => 'ws.courses.update',
		'uses' => 'AccountsController@update',
		'middleware' => 'auth:api|can:edit courses',
	]);
	$router->delete('{id}', [
		'as' => 'ws.courses.delete',
		'uses' => 'AccountsController@delete',
		'middleware' => 'auth:api|can:delete courses',
	]);
});

$router->get('classsync', [
	'as' => 'ws.courses.sync',
	'uses' => 'AccountsController@sync',
	'middleware' => 'auth:api',
]);

$router->group(['prefix' => 'classuser'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'ws.courses.members',
		'uses' => 'MembersController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'ws.courses.members.create',
		'uses' => 'MembersController@create',
		'middleware' => ['auth:api', 'can:edit courses,edit.own courses'],
	]);
	$router->get('{id}', [
		'as' => 'ws.courses.members.read',
		'uses' => 'MembersController@read',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'ws.courses.members.update',
		'uses' => 'MembersController@update',
		'middleware' => ['auth:api', 'can:edit courses,edit.own courses'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.courses.members.delete',
		'uses' => 'MembersController@delete',
		'middleware' => ['auth:api', 'can:edit courses,edit.own courses'],
	])->where('id', '[0-9]+');
});
