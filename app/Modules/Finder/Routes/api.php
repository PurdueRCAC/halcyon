<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'finder'], function (Router $router)
{
	$router->group(['prefix' => 'facets'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.finder.facets',
			'uses' => 'FacetsController@index',
		]);
		$router->post('/', [
			'as' => 'api.finder.facets.create',
			'uses' => 'FacetsController@create',
			'middleware' => ['auth:api', 'can:create finder'],
		]);
		$router->get('{id}', [
			'as' => 'api.finder.facets.read',
			'uses' => 'FacetsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.finder.facets.update',
			'uses' => 'FacetsController@update',
			'middleware' => ['auth:api', 'can:edit finder'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.finder.facets.delete',
			'uses' => 'FacetsController@delete',
			'middleware' => ['auth:api', 'can:delete finder'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'services'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.finder.services',
			'uses' => 'ServicesController@index',
		]);
		$router->post('/', [
			'as' => 'api.finder.services.create',
			'uses' => 'ServicesController@create',
			'middleware' => ['auth:api', 'can:create finder'],
		]);
		$router->get('{id}', [
			'as' => 'api.finder.services.read',
			'uses' => 'ServicesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.finder.services.update',
			'uses' => 'ServicesController@update',
			'middleware' => ['auth:api', 'can:edit finder'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.finder.services.delete',
			'uses' => 'ServicesController@delete',
			'middleware' => ['auth:api', 'can:delete finder'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'fields'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.finder.fields',
			'uses' => 'FieldsController@index',
		]);
		$router->post('/', [
			'as' => 'api.finder.fields.create',
			'uses' => 'FieldsController@create',
			'middleware' => ['auth:api', 'can:create finder'],
		]);
		$router->get('{id}', [
			'as' => 'api.finder.fields.read',
			'uses' => 'FieldsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.finder.fields.update',
			'uses' => 'FieldsController@update',
			'middleware' => ['auth:api', 'can:edit finder'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.finder.fields.delete',
			'uses' => 'FieldsController@delete',
			'middleware' => ['auth:api', 'can:delete finder'],
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@index',
	]);

	$router->post('/sendmail', [
		'as' => 'api.finder.sendmail',
		'uses' => 'FinderController@sendmail',
		'middleware' => ['auth:api', 'can:manage finder'],
	]);

	$router->get('/servicelist', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@servicelist',
	]);

	$router->get('/facettree', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@facettree',
	]);

	$router->get('/settings', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@settings',
	]);
});
