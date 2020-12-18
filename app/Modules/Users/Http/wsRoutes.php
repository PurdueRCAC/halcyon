<?php
// [!] Legacy compatibility
use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'user'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.users.index',
		'uses' => 'UsersController@index',
		//'middleware' => 'can:manage users',
	]);

	$router->post('/', [
		'as' => 'api.users.create',
		'uses' => 'UsersController@create',
		'middleware' => ['auth:api', 'can:create users'],
	]);

	$router->get('{id}', [
		'as' => 'api.users.read',
		'uses' => 'UsersController@read',
		//'middleware' => 'can:users.user.view',
	])->where('id', '[0-9]+');

	$router->put('{id}', [
		'as' => 'api.users.update',
		'uses' => 'UsersController@update',
		'middleware' => ['auth:api', 'can:edit users'],
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'api.users.delete',
		'uses' => 'UsersController@delete',
		'middleware' => ['auth:api', 'can:delete users'],
	])->where('id', '[0-9]+');
});
