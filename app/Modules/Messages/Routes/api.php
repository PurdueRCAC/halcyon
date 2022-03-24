<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'messages', 'middleware' => ['auth:api', 'can:manage messages']], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.messages.index',
		'uses' => 'MessagesController@index',
	]);
	$router->post('/', [
		'as' => 'api.messages.create',
		'uses' => 'MessagesController@create',
	]);
	$router->get('{id}', [
		'as'   => 'api.messages.read',
		'uses' => 'MessagesController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.messages.update',
		'uses' => 'MessagesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.messages.delete',
		'uses' => 'MessagesController@delete',
	])->where('id', '[0-9]+');

	// Types
	$router->group(['prefix' => 'types', 'can:manage messages.types'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.messages.types',
			'uses' => 'TypesController@index',
		]);
		$router->post('/', [
			'as' => 'api.messages.types.create',
			'uses' => 'TypesController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.messages.types.read',
			'uses' => 'TypesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.messages.types.update',
			'uses' => 'TypesController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.messages.types.delete',
			'uses' => 'TypesController@delete',
		])->where('id', '[0-9]+');
	});
});
