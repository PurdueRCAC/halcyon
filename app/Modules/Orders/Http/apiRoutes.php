<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'orders', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.orders.index',
		'uses' => 'OrdersController@index',
		//'middleware' => 'can:tag.orders.index',
	]);
	$router->post('/', [
		'as' => 'api.orders.create',
		'uses' => 'OrdersController@create',
		'middleware' => 'can:create orders',
	]);
	$router->get('{id}', [
		'as' => 'api.orders.read',
		'uses' => 'OrdersController@read',
		//'middleware' => 'can:tag.orders.edit',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.orders.update',
		'uses' => 'OrdersController@update',
		'middleware' => 'can:edit orders',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.orders.delete',
		'uses' => 'OrdersController@delete',
		'middleware' => 'can:delete orders',
	])->where('id', '[0-9]+');

	$router->get('/sequence/{id}', [
		'as' => 'api.orders.items.sequence',
		'uses' => 'ItemsController@sequence',
	])->where('id', '[0-9]+');

	// Categories
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

	// Products
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

	// Cart
	$router->group(['prefix' => '/cart', 'middleware' => 'can:create orders'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.orders.cart',
			'uses' => 'CartController@index',
		]);
		$router->post('/', [
			'as' => 'api.orders.cart.create',
			'uses' => 'CartController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.orders.cart.read',
			'uses' => 'CartController@read',
		])->where('id', '[a-z0-9]+');
		$router->put('{id}', [
			'as' => 'api.orders.cart.update',
			'uses' => 'CartController@update',
		])->where('id', '[a-z0-9]+');
		$router->delete('{id}', [
			'as' => 'api.orders.cart.delete',
			'uses' => 'CartController@delete',
		])->where('id', '[a-z0-9]+');
	});

	// Order Items
	$router->group(['prefix' => '/items'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.orders.items',
			'uses' => 'ItemsController@index',
		]);
		$router->post('/', [
			'as' => 'api.orders.items.create',
			'uses' => 'ItemsController@create',
			'middleware' => 'can:edit orders',
		]);
		$router->get('{id}', [
			'as' => 'api.orders.items.read',
			'uses' => 'ItemsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.orders.items.update',
			'uses' => 'ItemsController@update',
			'middleware' => 'can:edit orders',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.orders.items.delete',
			'uses' => 'ItemsController@delete',
			'middleware' => 'can:edit orders',
		])->where('id', '[0-9]+');
	});

	// Order accounts
	$router->group(['prefix' => '/accounts'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.orders.accounts',
			'uses' => 'AccountsController@index',
		]);
		$router->post('/', [
			'as' => 'api.orders.accounts.create',
			'uses' => 'AccountsController@create',
			'middleware' => 'can:edit orders',
		]);
		$router->get('{id}', [
			'as' => 'api.orders.accounts.read',
			'uses' => 'AccountsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.orders.accounts.update',
			'uses' => 'AccountsController@update',
			'middleware' => 'can:edit orders',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.orders.accounts.delete',
			'uses' => 'AccountsController@delete',
			'middleware' => 'can:edit orders',
		])->where('id', '[0-9]+');
	});
});
