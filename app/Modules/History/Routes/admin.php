<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'history'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.history.index',
		'uses' => 'HistoryController@index',
		'middleware' => 'can:manage history',
	]);

	/*$router->post('/store', [
		'as' => 'admin.history.store',
		'uses' => 'HistoryController@store',
		'middleware' => 'can:create history|edit history',
	]);*/

	$router->get('/{id}', [
		'as' => 'admin.history.show',
		'uses' => 'HistoryController@show',
	])->where('id', '[0-9]+');

	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.history.delete',
		'uses' => 'HistoryController@delete',
		'middleware' => 'can:delete history',
	]);

	$router->group(['prefix' => 'activity', 'middleware' => 'can:admin'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.history.activity',
			'uses' => 'ActivityController@index',
		]);

		$router->get('{id}', [
			'as' => 'admin.history.activity.show',
			'uses' => 'ActivityController@show',
		])->where('id', '[0-9]+');

		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.history.activity.delete',
			'uses' => 'ActivityController@delete',
		]);
	});

	$router->group(['prefix' => 'notifications', 'middleware' => 'can:admin'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.history.notifications',
			'uses' => 'NotificationsController@index',
		]);

		$router->get('{id}', [
			'as' => 'admin.history.notifications.show',
			'uses' => 'NotificationsController@show',
		])->where('id', '[a-z0-9\-]+');

		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.history.notifications.delete',
			'uses' => 'NotificationsController@delete',
		]);
	});

	$router->group(['prefix' => 'api', 'middleware' => 'can:admin'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.history.api',
			'uses' => 'ApiController@index',
		]);

		$router->get('stats', [
			'as' => 'admin.history.api.stats',
			'uses' => 'ApiController@stats',
		]);

		$router->get('{id}', [
			'as' => 'admin.history.api.show',
			'uses' => 'ApiController@show',
		])->where('id', '[0-9]+');
	});
});
