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
		'middleware' => ['auth:api', 'can:create news'],
	]);
	$router->post('/preview', [
		'as' => 'api.news.preview',
		'uses' => 'ArticlesController@preview',
		//'middleware' => 'auth:api',
	]);
	$router->get('{id}', [
		'as'   => 'api.news.read',
		'uses' => 'ArticlesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.news.update',
		'uses' => 'ArticlesController@update',
		//'middleware' => 'auth:api',
		'middleware' => ['auth:api', 'can:edit news'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.news.delete',
		'uses' => 'ArticlesController@delete',
		'middleware' => ['auth:api', 'can:delete news'],
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
			'middleware' => ['auth:api', 'can:create news.types'],
		]);
		$router->get('{id}', [
			'as' => 'api.news.types.read',
			'uses' => 'TypesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.news.types.update',
			'uses' => 'TypesController@update',
			'middleware' => ['auth:api', 'can:edit news.types'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.news.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => ['auth:api', 'can:delete news.types'],
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
			'middleware' => ['auth:api', 'can:edit news'],
		]);
		$router->get('{id}', [
			'as' => 'api.news.updates.read',
			'uses' => 'UpdatesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.news.updates.update',
			'uses' => 'UpdatesController@update',
			'middleware' => ['auth:api', 'can:edit news'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.news.updates.delete',
			'uses' => 'UpdatesController@delete',
			'middleware' => ['auth:api', 'can:edit news'],
		])->where('id', '[0-9]+');
	});

	$router->get('{id}/views', [
		'as'   => 'api.news.views',
		'uses' => 'ArticlesController@views',
	])->where('id', '[0-9]+');
});
