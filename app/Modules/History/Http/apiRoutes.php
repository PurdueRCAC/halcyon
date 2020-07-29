<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'history'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.history.index',
		'uses' => 'HistoryController@index',
		'middleware' => 'auth:api',
	]);
	$router->get('{id}', [
		'as'   => 'api.history.read',
		'uses' => 'HistoryController@read',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
});
