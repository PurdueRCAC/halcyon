<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => config('module.news.route', 'news')], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.news.index',
		'uses' => 'ArticlesController@index',
	]);
	$router->get('rss/', [
		'as' => 'site.news.rss',
		'uses' => 'ArticlesController@rss',
	]);
	$router->get('rss/{name}', [
		'as' => 'site.news.feed',
		'uses' => 'ArticlesController@feed',
	]);//->where('name', '[a-zA-Z0-9\-_,\+\% ]+');
	$router->get('search', [
		'as' => 'site.news.search',
		'uses' => 'ArticlesController@search',
	]);
	$router->get('calendar/{name}', [
		'as' => 'site.news.calendar',
		'uses' => 'ArticlesController@calendar',
	])->where('name', '[a-zA-Z0-9\-_\+\% ]+');

	$router->get('manage', [
		'as' => 'site.news.manage',
		'uses' => 'ArticlesController@manage',
		'middleware' => 'can:create news',
	]);
	$router->post('store', [
		'as' => 'site.news.store',
		'uses' => 'ArticlesController@store',
		'middleware' => 'can:create news',
	]);

	$router->get('{id}', [
		'as' => 'site.news.show',
		'uses' => 'ArticlesController@show',
	])->where('id', '[0-9]+');
	$router->get('{id}/edit', [
		'as' => 'site.news.edit',
		'uses' => 'ArticlesController@edit',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'site.news.update',
		'uses' => 'ArticlesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'site.news.destroy',
		'uses' => 'ArticlesController@destroy',
	])->where('id', '[0-9]+');

	$router->get('{name}', [
		'as' => 'site.news.type',
		'uses' => 'ArticlesController@type',
	])->where('name', '[a-zA-Z0-9\-_\+\% ]+');

	$router->get('{id}/visit/{token}', [
		'as' => 'site.news.visit',
		'uses' => 'ArticlesController@visit',
	])->where('id', '[0-9]+'); //->where('token', '[0-9]+');
});
