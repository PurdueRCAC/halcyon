<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'orders', 'middleware' => 'auth'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.orders.index',
		'uses' => 'OrdersController@index',
	]);
	$router->get('create', [
		'as' => 'site.orders.create',
		'uses' => 'OrdersController@create',
		'middleware' => 'can:create orders',
	]);
	$router->post('store', [
		'as' => 'site.orders.store',
		'uses' => 'OrdersController@store',
		'middleware' => 'can:create orders|edit orders',
	]);
	$router->get('view/{id}', [
		'as' => 'site.orders.read',
		'uses' => 'OrdersController@edit',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');

	/*$router->get('{id}/edit', [
		'as' => 'site.orders.edit',
		'uses' => 'OrdersController@edit',
		'middleware' => 'can:edit orders',
	])->where('id', '[0-9]+');

	$router->put('{id}', [
		'as' => 'site.orders.update',
		'uses' => 'OrdersController@update',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');*/

	$router->get('cart', [
		'as' => 'site.orders.cart',
		'uses' => 'OrdersController@cart',
		'middleware' => 'can:create orders',
	]);
	$router->post('import', [
		'as' => 'site.orders.import',
		'uses' => 'OrdersController@import',
		'middleware' => 'can:manage orders',
	]);
	$router->post('process', [
		'as' => 'site.orders.process',
		'uses' => 'OrdersController@process',
		'middleware' => 'can:manage orders',
	]);

	/*$router->get('recur', [
		'as' => 'site.orders.recurring',
		'uses' => 'OrdersController@recurring',
		'middleware' => 'can:manage orders',
	]);

	$router->get('delete/{id}', [
		'as' => 'site.orders.delete',
		'uses' => 'OrdersController@delete',
		'middleware' => 'can:delete orders',
	])->where('id', '[0-9]+');*/

	// Recurring items
	$router->group(['prefix' => '/recur'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'site.orders.recurring',
			'uses' => 'OrdersController@recurring',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/{id}', [
			'as' => 'site.orders.recurring.read',
			'uses' => 'OrdersController@recurringitem',
			'middleware' => 'can:manage orders',
		])->where('id', '[0-9]+');
	});

	// Categories
	$router->group(['prefix' => '/categories'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'site.orders.categories',
			'uses' => 'CategoriesController@index',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'site.orders.categories.create',
			'uses' => 'CategoriesController@create',
			'middleware' => 'can:create orders.categories',
		]);
		$router->post('/store', [
			'as' => 'site.orders.categories.store',
			'uses' => 'CategoriesController@store',
			'middleware' => 'can:create orders.categories|edit orders.categories',
		]);
		$router->get('/edit/{id}', [
			'as' => 'site.orders.categories.edit',
			'uses' => 'CategoriesController@edit',
			'middleware' => 'can:edit orders.categories',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'site.orders.categories.delete',
			'uses' => 'CategoriesController@delete',
			'middleware' => 'can:delete orders.categories',
		])->where('id', '[0-9]+');
		$router->post('/cancel', [
			'as' => 'site.orders.categories.cancel',
			'uses' => 'CategoriesController@cancel',
		]);
	});

	// Products
	$router->group(['prefix' => '/products'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'site.orders.products',
			'uses' => 'ProductsController@index',
		]);
		$router->match(['get', 'post'], '/manage', [
			'as'   => 'site.orders.products.manage',
			'uses' => 'ProductsController@manage',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'site.orders.products.create',
			'uses' => 'ProductsController@create',
			'middleware' => 'can:create orders',
		]);
		$router->post('/store', [
			'as' => 'site.orders.products.store',
			'uses' => 'ProductsController@store',
			'middleware' => 'can:create orders|edit orders',
		]);
		$router->get('/edit/{id}', [
			'as' => 'site.orders.products.edit',
			'uses' => 'ProductsController@edit',
			'middleware' => 'can:edit orders',
		]);
		$router->get('/view/{id}', [
			'as' => 'site.orders.products.read',
			'uses' => 'ProductsController@read',
			//'middleware' => 'can:edit orders',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'site.orders.products.delete',
			'uses' => 'ProductsController@delete',
			'middleware' => 'can:delete orders',
		]);
		$router->post('/cancel', [
			'as' => 'site.orders.products.cancel',
			'uses' => 'ProductsController@cancel',
		]);
	});
});

// Legacy routes
$router->group(['prefix' => 'order', 'middleware' => 'auth'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.order.index',
		'uses' => 'OrdersController@index',
	]);
	$router->get('/view', [
		'as' => 'site.order.view',
		'uses' => 'OrdersController@index',
	]);
	$router->get('create', [
		'as' => 'site.order.create',
		'uses' => 'OrdersController@create',
		'middleware' => 'can:create orders',
	]);
	$router->post('store', [
		'as' => 'site.order.store',
		'uses' => 'OrdersController@store',
		'middleware' => 'can:create orders|edit orders',
	]);
	$router->get('{id}', [
		'as' => 'site.order.quickread',
		'uses' => 'OrdersController@edit',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');

	$router->get('/view/{id}', [
		'as' => 'site.order.read',
		'uses' => 'OrdersController@edit',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');

	$router->get('cart', [
		'as' => 'site.order.cart',
		'uses' => 'OrdersController@cart',
		'middleware' => 'can:create orders',
	]);

	// Recurring items
	$router->group(['prefix' => '/recur'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'site.order.recurring',
			'uses' => 'OrdersController@recurring',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/{id}', [
			'as' => 'site.order.recurring.read',
			'uses' => 'OrdersController@recurringitem',
			'middleware' => 'can:manage orders',
		])->where('id', '[0-9]+');
	});

	// Categories
	$router->group(['prefix' => '/categories'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'site.order.categories',
			'uses' => 'CategoriesController@index',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'site.order.categories.create',
			'uses' => 'CategoriesController@create',
			'middleware' => 'can:create orders.categories',
		]);
		$router->post('/store', [
			'as' => 'site.order.categories.store',
			'uses' => 'CategoriesController@store',
			'middleware' => 'can:create orders.categories|edit orders.categories',
		]);
		$router->get('/edit/{id}', [
			'as' => 'site.order.categories.edit',
			'uses' => 'CategoriesController@edit',
			'middleware' => 'can:edit orders.categories',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'site.order.categories.delete',
			'uses' => 'CategoriesController@delete',
			'middleware' => 'can:delete orders.categories',
		])->where('id', '[0-9]+');
		$router->post('/cancel', [
			'as' => 'site.order.categories.cancel',
			'uses' => 'CategoriesController@cancel',
		]);
	});

	// Products
	$router->group(['prefix' => '/products'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'site.order.products',
			'uses' => 'ProductsController@index',
		]);
		$router->match(['get', 'post'], '/manage', [
			'as'   => 'site.order.products.manage',
			'uses' => 'ProductsController@manage',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'site.order.products.create',
			'uses' => 'ProductsController@create',
			'middleware' => 'can:create orders',
		]);
		$router->post('/store', [
			'as' => 'site.order.products.store',
			'uses' => 'ProductsController@store',
			'middleware' => 'can:create orders|edit orders',
		]);
		$router->get('/edit/{id}', [
			'as' => 'site.order.products.edit',
			'uses' => 'ProductsController@edit',
			'middleware' => 'can:edit orders',
		]);
		$router->get('/view/{id}', [
			'as' => 'site.order.products.read',
			'uses' => 'ProductsController@read',
			//'middleware' => 'can:edit orders',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'site.order.products.delete',
			'uses' => 'ProductsController@delete',
			'middleware' => 'can:delete orders',
		]);
		$router->post('/cancel', [
			'as' => 'site.order.products.cancel',
			'uses' => 'ProductsController@cancel',
		]);
	});
});