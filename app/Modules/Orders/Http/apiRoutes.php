<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'orders'], function (Router $router)
{
	$router->get('/', [
		'as' => 'admin.orders.index',
		'uses' => 'OrdersController@index',
		//'middleware' => 'can:tag.orders.index',
	]);
	$router->post('/', [
		'as' => 'admin.orders.create',
		'uses' => 'OrdersController@create',
		'middleware' => 'can:create orders',
	]);
	$router->get('{id}', [
		'as' => 'admin.orders.read',
		'uses' => 'OrdersController@read',
		//'middleware' => 'can:tag.orders.edit',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'admin.orders.update',
		'uses' => 'OrdersController@update',
		'middleware' => 'can:edit orders',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'admin.orders.delete',
		'uses' => 'OrdersController@delete',
		'middleware' => 'can:delete orders',
	])->where('id', '[0-9]+');

	// Comments
	$router->group(['prefix' => '/categories'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.orders.categories',
			'uses' => 'CategoriesController@index',
		]);
		$router->post('/', [
			'as' => 'api.orders.categories.create',
			'uses' => 'CategoriesController@create',
			'middleware' => 'can:create orders.categories',
		]);
		$router->get('{id}', [
			'as' => 'api.orders.categories.read',
			'uses' => 'CategoriesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.orders.categories.update',
			'uses' => 'CategoriesController@update',
			'middleware' => 'can:edit orders.categories',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.orders.categories.delete',
			'uses' => 'CategoriesController@delete',
			'middleware' => 'can:delete orders.categories',
		])->where('id', '[0-9]+');
	});

	// Comments
	$router->group(['prefix' => '/products'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.orders.products',
			'uses' => 'ProductsController@index',
		]);
		$router->post('/', [
			'as' => 'api.orders.products.create',
			'uses' => 'ProductsController@create',
			'middleware' => 'can:create orders.products',
		]);
		$router->get('{id}', [
			'as' => 'api.orders.products.read',
			'uses' => 'ProductsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.orders.products.update',
			'uses' => 'ProductsController@update',
			'middleware' => 'can:edit orders.products',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.orders.products.delete',
			'uses' => 'ProductsController@delete',
			'middleware' => 'can:delete orders.products',
		])->where('id', '[0-9]+');
	});
});
