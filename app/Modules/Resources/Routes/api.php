<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'resources', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.resources.types',
			'uses' => 'TypesController@index',
		]);
		$router->post('/', [
			'as' => 'api.resources.types.create',
			'uses' => 'TypesController@create',
			'middleware' => ['can:create resources.types'],
		]);
		$router->get('{id}', [
			'as' => 'api.resources.types.read',
			'uses' => 'TypesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.resources.types.update',
			'uses' => 'TypesController@update',
			'middleware' => ['can:edit resources.types'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.resources.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => ['can:delete resources.types'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'batchsystems'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.resources.batchsystems',
			'uses' => 'BatchsystemsController@index',
		]);
		$router->post('/', [
			'as' => 'api.resources.batchsystems.create',
			'uses' => 'BatchsystemsController@create',
			'middleware' => ['can:create resources.batchsystems'],
		]);
		$router->get('{id}', [
			'as' => 'api.resources.batchsystems.read',
			'uses' => 'BatchsystemsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.resources.batchsystems.update',
			'uses' => 'BatchsystemsController@update',
			'middleware' => ['can:edit resources.batchsystems'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.resources.batchsystems.delete',
			'uses' => 'BatchsystemsController@delete',
			'middleware' => ['can:delete resources.batchsystems'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'subresources'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.resources.subresources',
			'uses' => 'SubresourcesController@index',
		]);
		$router->post('/', [
			'as' => 'api.resources.subresources.create',
			'uses' => 'SubresourcesController@create',
			'middleware' => 'can:create resources.subresources',
		]);
		$router->get('{id}', [
			'as' => 'api.resources.subresources.read',
			'uses' => 'SubresourcesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.resources.subresources.update',
			'uses' => 'SubresourcesController@update',
			'middleware' => 'can:edit resources.subresources',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.resources.subresources.delete',
			'uses' => 'SubresourcesController@delete',
			'middleware' => 'can:delete resources.subresources',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->get('/{id?}', [
			'as' => 'api.resources.members',
			'uses' => 'MembersController@index',
		])->where('id', '[0-9]+');

		$router->post('/', [
			'as' => 'api.resources.members.create',
			'uses' => 'MembersController@create',
			'middleware' => 'can:create resources.members',
		]);

		$router->get('{id}', [
			'as' => 'api.resources.members.read',
			'uses' => 'MembersController@read',
		])->where('id', '[0-9]+\.[0-9]+');

		$router->delete('{id}', [
			'as' => 'api.resources.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => 'can:delete resources.members',
		])->where('id', '[0-9]+\.[0-9]+');
	});

	$router->get('/', [
		'as' => 'api.resources.index',
		'uses' => 'ResourcesController@index',
	]);

	$router->post('/', [
		'as' => 'api.resources.create',
		'uses' => 'ResourcesController@create',
		'middleware' => ['can:create resources'],
	]);

	$router->get('{id}', [
		'as' => 'api.resources.read',
		'uses' => 'ResourcesController@read',
	])->where('id', '[0-9]+');

	$router->get('{id}/members', [
		'as' => 'api.resources.read.members',
		'uses' => 'ResourcesController@members',
		'middleware' => ['can:manage resources'],
	])->where('id', '[0-9]+');

	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.resources.update',
		'uses' => 'ResourcesController@update',
		'middleware' => ['can:edit resources'],
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'api.resources.delete',
		'uses' => 'ResourcesController@delete',
		'middleware' => ['can:delete resources'],
	])->where('id', '[0-9]+');
});
