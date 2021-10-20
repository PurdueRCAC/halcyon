<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'widgets'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.widgets.index',
		'uses' => 'WidgetsController@index',
	]);
	$router->post('/', [
		'as' => 'api.widgets.create',
		'uses' => 'WidgetsController@create',
		'middleware' => 'can:create widgets',
	]);
	$router->get('{id}', [
		'as'   => 'api.widgets.read',
		'uses' => 'WidgetsController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.widgets.update',
		'uses' => 'WidgetsController@update',
		'middleware' => 'can:edit widgets',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.widgets.delete',
		'uses' => 'WidgetsController@delete',
		'middleware' => 'can:delete widgets',
	])->where('id', '[0-9]+');
});
