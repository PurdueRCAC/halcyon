<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'news'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.news.index',
		'uses' => 'ArticlesController@index',
	]);
	$router->post('/', [
		'as' => 'api.news.create',
		'uses' => 'ArticlesController@create',
		'middleware' => 'auth:api',
	]);
	$router->get('{id}', [
		'as'   => 'api.news.read',
		'uses' => 'ArticlesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.news.update',
		'uses' => 'ArticlesController@update',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.news.delete',
		'uses' => 'ArticlesController@delete',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');

	// Types
	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.news.types',
			'uses' => 'TypesController@index',
		]);
		$router->post('/', [
			'as' => 'api.news.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'auth:api',
		]);
		$router->get('{id}', [
			'as' => 'api.news.types.read',
			'uses' => 'TypesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.news.types.update',
			'uses' => 'TypesController@update',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.news.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
	});

	// Updates
	$router->group(['prefix' => '{news_id}/updates'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.news.updates',
			'uses' => 'UpdatesController@index',
		]);
		$router->post('/', [
			'as' => 'api.news.updates.create',
			'uses' => 'UpdatesController@create',
			'middleware' => 'auth:api',
		]);
		$router->get('{id}', [
			'as' => 'api.news.updates.read',
			'uses' => 'UpdatesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.news.updates.update',
			'uses' => 'UpdatesController@update',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.news.updates.delete',
			'uses' => 'UpdatesController@delete',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
	});
});
