<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'menus', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.menus.index',
		'uses' => 'MenusController@index',
	]);
	$router->post('/', [
		'as' => 'api.menus.create',
		'uses' => 'MenusController@create',
		'middleware' => ['can:create menus'],
	]);
	$router->get('{id}', [
		'as'   => 'api.menus.read',
		'uses' => 'MenusController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.menus.update',
		'uses' => 'MenusController@update',
		'middleware' => ['can:edit menus'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.menus.delete',
		'uses' => 'MenusController@delete',
		'middleware' => ['can:delete menus'],
	])->where('id', '[0-9]+');

	// Updates
	$router->group(['prefix' => 'items', 'middleware' => ['can:edit menus'],], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.menus.items',
			'uses' => 'ItemsController@index',
		]);
		$router->post('/', [
			'as' => 'api.menus.items.create',
			'uses' => 'ItemsController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.menus.items.read',
			'uses' => 'ItemsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.menus.items.update',
			'uses' => 'ItemsController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.menus.items.delete',
			'uses' => 'ItemsController@delete',
		])->where('id', '[0-9]+');
	});
});
