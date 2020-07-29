<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'listeners'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.listeners.index',
		'uses' => 'ListenersController@index',
	]);
	$router->post('/', [
		'as' => 'api.listeners.create',
		'uses' => 'ListenersController@create',
		'middleware' => 'can:create listeners',
	]);
	$router->get('{id}', [
		'as'   => 'api.listeners.read',
		'uses' => 'ListenersController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.listeners.update',
		'uses' => 'ListenersController@update',
		'middleware' => 'can:edit listeners',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.listeners.delete',
		'uses' => 'ListenersController@delete',
		'middleware' => 'can:delete listeners',
	])->where('id', '[0-9]+');
});
