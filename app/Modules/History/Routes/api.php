<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'history', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.history.index',
		'uses' => 'HistoryController@index',
	]);
	$router->get('{id}', [
		'as'   => 'api.history.read',
		'uses' => 'HistoryController@read',
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'logs', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.logs.index',
		'uses' => 'LogsController@index',
	]);
	/*$router->post('/', [
		'as' => 'api.logs.create',
		'uses' => 'LogsController@create',
	]);*/
	$router->get('{id}', [
		'as' => 'api.logs.read',
		'uses' => 'LogsController@read',
	])->where('id', '[0-9]+');
});
