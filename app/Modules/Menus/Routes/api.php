<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'menus'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.menus.index',
		'uses' => 'MenusController@index',
	]);
	$router->post('/', [
		'as' => 'api.menus.create',
		'uses' => 'MenusController@create',
	]);
	$router->get('{id}', [
		'as'   => 'api.menus.read',
		'uses' => 'MenusController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.menus.update',
		'uses' => 'MenusController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.menus.delete',
		'uses' => 'MenusController@delete',
	])->where('id', '[0-9]+');

	// Updates
	$router->group(['prefix' => 'items'], function (Router $router)
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
