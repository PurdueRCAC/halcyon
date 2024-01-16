<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'publications', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.publications.index',
		'uses' => 'PublicationsController@index',
	]);
	$router->post('/', [
		'as' => 'api.publications.create',
		'uses' => 'PublicationsController@create',
		'middleware' => ['can:create publications'],
	]);
	$router->get('{id}', [
		'as'   => 'api.publications.read',
		'uses' => 'PublicationsController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.publications.update',
		'uses' => 'PublicationsController@update',
		'middleware' => ['can:edit publications'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.publications.delete',
		'uses' => 'PublicationsController@delete',
		'middleware' => ['can:delete publications'],
	])->where('id', '[0-9]+');

	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'api.publications.types',
			'uses' => 'TypesController@index',
		]);
		$router->get('/create', [
			'as'   => 'api.publications.types.create',
			'uses' => 'TypesController@create',
			'middleware' => ['can:create publications'],
		]);
		$router->post('/store', [
			'as'   => 'api.publications.types.store',
			'uses' => 'TypesController@store',
			'middleware' => ['can:create publications|edit publications'],
		]);
		$router->get('/{id}', [
			'as'   => 'api.publications.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => ['can:edit publications'],
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'api.publications.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => ['can:delete publications'],
		]);
	});

	/*$router->group(['prefix' => 'authors', 'middleware' => ['can:manage publications']], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.publications.authors',
			'uses' => 'AuthorsController@index',
		]);
		$router->post('/', [
			'as' => 'api.publications.authors.create',
			'uses' => 'AuthorsController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.publications.authors.read',
			'uses' => 'AuthorsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.publications.authors.update',
			'uses' => 'AuthorsController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.publications.authors.delete',
			'uses' => 'AuthorsController@delete',
		])->where('id', '[0-9]+');
	});*/
});
