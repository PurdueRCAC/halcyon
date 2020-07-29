<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('login', [
	//'middleware' => 'auth.guest',
	'as'   => 'admin.login',
	'uses' => 'AuthController@login'
]);
$router->post('login', [
	'as'   => 'admin.login.post',
	'uses' => 'AuthController@authenticate'
]);

$router->get('logout', [
	'as'   => 'admin.logout',
	'uses' => 'AuthController@logout'
]);

$router->group(['prefix' => 'users'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.users.index',
		'uses' => 'UsersController@index',
		'middleware' => 'can:manage users',
	]);
	$router->get('create', [
		'as' => 'admin.users.create',
		'uses' => 'UsersController@create',
		'middleware' => 'can:create users',
	]);
	$router->get('ingest', [
		'as' => 'admin.users.ingest',
		'uses' => 'UsersController@ingest',
		'middleware' => 'can:create users',
	]);
	$router->post('store', [
		'as' => 'admin.users.store',
		'uses' => 'UsersController@store',
		'middleware' => 'can:create users,edit users',
	]);
	$router->match(['get', 'post'], '/block/{id?}', [
		'as' => 'admin.users.block',
		'uses' => 'UsersController@block',
		'middleware' => 'can:edit.state users',
	]);
	$router->match(['get', 'post'], '/unblock/{id?}', [
		'as' => 'admin.users.unblock',
		'uses' => 'UsersController@unblock',
		'middleware' => 'can:edit.state users',
	]);
	$router->match(['get', 'post'], '/confirm/{id?}', [
		'as' => 'admin.users.confirm',
		'uses' => 'UsersController@block',
		'middleware' => 'can:edit.state users',
	]);
	$router->match(['get', 'post'], '/unconfirm/{id?}', [
		'as' => 'admin.users.unconfirm',
		'uses' => 'UsersController@unblock',
		'middleware' => 'can:edit.state users',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as' => 'admin.users.delete',
		'uses' => 'UsersController@delete',
		'middleware' => 'can:delete users',
	]);
	$router->get('/debug/{id}', [
		'as' => 'admin.users.debug',
		'uses' => 'UsersController@debug',
		'middleware' => 'can:manage users',
	]);

	$router->group(['prefix' => 'notes'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'admin.users.notes',
			'uses' => 'NotesController@index',
			'middleware' => 'can:manage users.notes',
		]);
		$router->get('create', [
			'as' => 'admin.users.notes.create',
			'uses' => 'NotesController@create',
			'middleware' => 'can:create users.notes',
		]);
		$router->post('store', [
			'as' => 'admin.users.notes.store',
			'uses' => 'NotesController@store',
			'middleware' => 'can:create users.notes,edit users.notes',
		]);
		$router->get('{id}', [
			'as' => 'admin.users.notes.edit',
			'uses' => 'NotesController@edit',
			'middleware' => 'can:edit users.notes',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.users.notes.delete',
			'uses' => 'NotesController@delete',
			'middleware' => 'can:delete users.notes',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.users.notes.cancel',
			'uses' => 'NotesController@cancel',
		]);
	});

	$router->group(['prefix' => 'roles'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.users.roles',
			'uses' => 'RolesController@index',
			'middleware' => 'can:manage users.roles',
		]);
		$router->get('create', [
			'as' => 'admin.users.roles.create',
			'uses' => 'RolesController@create',
			'middleware' => 'can:create users.roles',
		]);
		$router->post('store', [
			'as' => 'admin.users.roles.store',
			'uses' => 'RolesController@store',
			'middleware' => 'can:create users.roles,edit users.roles',
		]);
		$router->post('update', [
			'as' => 'admin.users.roles.update',
			'uses' => 'RolesController@update',
			'middleware' => 'can:manage users.roles',
		]);
		$router->get('{id}', [
			'as' => 'admin.users.roles.edit',
			'uses' => 'RolesController@edit',
			'middleware' => 'can:edit users.notes',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.users.roles.delete',
			'uses' => 'RolesController@delete',
			'middleware' => 'can:delete users.notes',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.users.roles.cancel',
			'uses' => 'RolesController@cancel',
		]);
	});

	$router->group(['prefix' => 'levels'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'admin.users.levels',
			'uses' => 'LevelsController@index',
			//'middleware' => 'can:users.levels',
		]);
		$router->get('create', [
			'as' => 'admin.users.levels.create',
			'uses' => 'LevelsController@create',
			'middleware' => 'can:create users.levels',
		]);
		$router->post('store', [
			'as' => 'admin.users.levels.store',
			'uses' => 'LevelsController@store',
			'middleware' => 'can:create users.levels,edit users.levels',
		]);
		$router->get('{id}', [
			'as' => 'admin.users.levels.edit',
			'uses' => 'LevelsController@edit',
			'middleware' => 'can:edit users.levels',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.users.levels.delete',
			'uses' => 'LevelsController@delete',
			'middleware' => 'can:delete users.levels',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.users.levels.cancel',
			'uses' => 'LevelsController@cancel',
		]);
	});

	/*$router->group(['prefix' => 'permissions'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'admin.users.permissions',
			'uses' => 'PermissionsController@index',
			//'middleware' => 'can:users.levels',
		]);
		$router->post('store', [
			'as' => 'admin.users.permissions.store',
			'uses' => 'PermissionsController@store',
			//'middleware' => 'can:create users.levels,edit users.levels',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.users.permissions.cancel',
			'uses' => 'PermissionsController@cancel',
		]);
	});*/

	$router->get('{id}', [
		'as' => 'admin.users.edit',
		'uses' => 'UsersController@edit',
		'middleware' => 'can:edit users',
	])->where('id', '[0-9]+');

	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.users.cancel',
		'uses' => 'UsersController@cancel',
	]);
});
