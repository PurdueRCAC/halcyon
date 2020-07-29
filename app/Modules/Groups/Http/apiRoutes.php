<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'groups'], function (Router $router)
{
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.members',
			'uses' => 'MembersController@index',
			'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.members.create',
			'uses' => 'MembersController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.members.read',
			'uses' => 'MembersController@read',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.members.update',
			'uses' => 'MembersController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => 'auth:api|can:edit groups,edit.own groups',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'motd'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.motd',
			'uses' => 'MotdController@index',
			'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.motd.create',
			'uses' => 'MotdController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.motd.read',
			'uses' => 'MotdController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.motd.update',
			'uses' => 'MotdController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.motd.delete',
			'uses' => 'MotdController@delete',
			'middleware' => 'auth:api|can:edit groups,edit.own groups',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'departments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.departments',
			'uses' => 'DepartmentsController@index',
			//'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.departments.create',
			'uses' => 'DepartmentsController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.departments.read',
			'uses' => 'DepartmentsController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.departments.update',
			'uses' => 'DepartmentsController@update',
			'middleware' => ['auth:api', 'can:edit groups.departments'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.departments.delete',
			'uses' => 'DepartmentsController@delete',
			'middleware' => ['auth:api', 'can:delete groups.departments'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'fieldsofscience'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.fieldsofscience',
			'uses' => 'FieldsOfScienceController@index',
			//'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.fieldsofscience.create',
			'uses' => 'FieldsOfScienceController@create',
			'middleware' => ['auth:api', 'can:edit groups.fieldsofscience'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.fieldsofscience.read',
			'uses' => 'FieldsOfScienceController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.fieldsofscience.update',
			'uses' => 'FieldsOfScienceController@update',
			'middleware' => ['auth:api', 'can:edit groups.fieldsofscience'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.fieldsofscience.delete',
			'uses' => 'FieldsOfScienceController@delete',
			'middleware' => ['auth:api', 'can:delete groups.fieldsofscience'],
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as' => 'api.groups.index',
		'uses' => 'GroupsController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'api.groups.create',
		'uses' => 'GroupsController@create',
		'middleware' => ['auth:api', 'can:create groups'],
	]);
	$router->get('{id}', [
		'as' => 'api.groups.read',
		'uses' => 'GroupsController@read',
		'middleware' => 'auth:api',
	]);
	$router->put('{id}', [
		'as' => 'api.groups.update',
		'uses' => 'GroupsController@update',
		'middleware' => 'auth:api|can:edit groups',
	]);
	$router->delete('{id}', [
		'as' => 'api.groups.delete',
		'uses' => 'GroupsController@delete',
		'middleware' => 'auth:api|can:delete groups',
	]);
});
