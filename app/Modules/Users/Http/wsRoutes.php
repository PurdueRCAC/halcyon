<?php
// [!] Legacy compatibility
use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'user'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.users.index',
		'uses' => 'UsersController@index',
		//'middleware' => 'can:manage users',
	]);

	$router->post('/', [
		'as' => 'ws.users.create',
		'uses' => 'UsersController@create',
		'middleware' => ['auth:api', 'can:create users'],
	]);

	$router->get('{id}', [
		'as' => 'ws.users.read',
		'uses' => 'UsersController@read',
		//'middleware' => 'can:users.user.view',
	])->where('id', '[0-9]+');

	$router->put('{id}', [
		'as' => 'ws.users.update',
		'uses' => 'UsersController@update',
		'middleware' => ['auth:api', 'can:edit users'],
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'ws.users.delete',
		'uses' => 'UsersController@delete',
		'middleware' => ['auth:api', 'can:delete users'],
	])->where('id', '[0-9]+');
});
