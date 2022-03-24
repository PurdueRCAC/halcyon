<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'cron', 'middleware' => ['auth:api', 'can:manage cron']], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.cron.index',
		'uses' => 'JobsController@index',
	]);
	$router->post('/', [
		'as' => 'api.cron.create',
		'uses' => 'JobsController@create',
		'middleware' => 'auth:api',
	]);
	$router->get('{id}', [
		'as'   => 'api.cron.read',
		'uses' => 'JobsController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.cron.update',
		'uses' => 'JobsController@update',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.cron.delete',
		'uses' => 'JobsController@delete',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
});
