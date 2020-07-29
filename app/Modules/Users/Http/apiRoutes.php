<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'users'], function (Router $router)
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

	$router->group(['prefix' => 'levels', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.users.levels',
			'uses' => 'LevelsController@index',
			//'middleware' => 'can:users.levels',
		]);
		$router->post('/', [
			'as' => 'api.users.levels.create',
			'uses' => 'LevelsController@create',
			'middleware' => 'can:create users.levels',
		]);
		$router->get('{id}', [
			'as' => 'api.users.levels.read',
			'uses' => 'LevelsController@read',
		])->where('id', '[0-9]+');
		$router->put('/', [
			'as' => 'api.users.levels.update',
			'uses' => 'LevelsController@update',
			'middleware' => 'can:edit users.levels',
		]);
		$router->delete('{id}', [
			'as'   => 'api.users.levels.delete',
			'uses' => 'LevelsController@delete',
			'middleware' => 'can:delete users.levels',
		]);
	});

	$router->group(['prefix' => 'roles', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.users.roles',
			'uses' => 'RolesController@index',
		]);
		$router->post('/', [
			'as' => 'api.users.roles.create',
			'uses' => 'RolesController@create',
			'middleware' => 'can:create users.roles',
		]);
		$router->get('{id}', [
			'as' => 'api.users.roles.read',
			'uses' => 'RolesController@read',
		])->where('id', '[0-9]+');
		$router->put('/', [
			'as' => 'api.users.roles.update',
			'uses' => 'RolesController@update',
			'middleware' => 'can:edit users.roles',
		]);
		$router->delete('{id}', [
			'as'   => 'api.users.roles.delete',
			'uses' => 'RolesController@delete',
			'middleware' => 'can:delete users.roles',
		]);
	});
});
