<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'news', 'middleware' => 'can:manage news'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.news.index',
		'uses' => 'ArticlesController@index',
		//'middleware' => 'can:manage news',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.news.cancel',
		'uses' => 'ArticlesController@cancel',
	]);
	$router->get('/create', [
		'as' => 'admin.news.create',
		'uses' => 'ArticlesController@create',
		'middleware' => 'can:create news',
	]);
	$router->post('/store', [
		'as' => 'admin.news.store',
		'uses' => 'ArticlesController@store',
		'middleware' => 'can:create news|edit news',
	]);
	$router->get('/publish/{id}', [
		'as'   => 'admin.news.publish',
		'uses' => 'ArticlesController@state',
		'middleware' => 'can:edit.state news',
	]);
	$router->get('/unpublish/{id}', [
		'as'   => 'admin.news.unpublish',
		'uses' => 'ArticlesController@state',
		'middleware' => 'can:edit.state news',
	]);
	$router->get('/edit/{id}', [
		'as' => 'admin.news.edit',
		'uses' => 'ArticlesController@edit',
		'middleware' => 'can:edit news',
	]);
	$router->match(['get', 'post'], '/templates', [
		'as'   => 'admin.news.templates',
		'uses' => 'ArticlesController@templates',
		'middleware' => 'can:manage news',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.news.delete',
		'uses' => 'ArticlesController@delete',
		'middleware' => 'can:delete news',
	]);

	// Types
	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.news.types',
			'uses' => 'TypesController@index',
			'middleware' => 'can:manage news',
		]);
		$router->get('/create', [
			'as' => 'admin.news.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create news.types',
		]);
		$router->post('/store', [
			'as' => 'admin.news.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create news.types|edit news.types',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.news.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit news.types',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.news.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete news.types',
		]);
		$router->post('/cancel', [
			'as' => 'admin.news.types.cancel',
			'uses' => 'TypesController@cancel',
		]);
	});

	// Updates
	$router->group(['prefix' => '{article}/updates'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.news.updates',
			'uses' => 'UpdatesController@index',
			'middleware' => 'can:manage news',
		]);
		$router->get('/create', [
			'as' => 'admin.news.updates.create',
			'uses' => 'UpdatesController@create',
			'middleware' => 'can:create news',
		]);
		$router->post('/store', [
			'as' => 'admin.news.updates.store',
			'uses' => 'UpdatesController@store',
			'middleware' => 'can:create news|edit news',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.news.updates.edit',
			'uses' => 'UpdatesController@edit',
			'middleware' => 'can:edit news',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.news.updates.delete',
			'uses' => 'UpdatesController@delete',
			'middleware' => 'can:delete news',
		]);
		$router->post('/cancel', [
			'as' => 'admin.news.updates.cancel',
			'uses' => 'UpdatesController@cancel',
		]);
	});
});
