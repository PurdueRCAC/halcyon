<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'orders', 'middleware' => 'can:manage orders'], function (Router $router)
{
	// Categories
	$router->group(['prefix' => 'categories'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.orders.categories',
			'uses' => 'CategoriesController@index',
			//'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'admin.orders.categories.create',
			'uses' => 'CategoriesController@create',
			'middleware' => 'can:create orders.categories',
		]);
		$router->post('/store', [
			'as' => 'admin.orders.categories.store',
			'uses' => 'CategoriesController@store',
			'middleware' => 'can:create orders.categories|edit orders.categories',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.orders.categories.edit',
			'uses' => 'CategoriesController@edit',
			'middleware' => 'can:edit orders.categories',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.orders.categories.delete',
			'uses' => 'CategoriesController@delete',
			'middleware' => 'can:delete orders.categories',
		]);
		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'admin.orders.categories.orderup',
			'uses' => 'CategoriesController@reorder',
			'middleware' => 'can:edit.state orders',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'admin.orders.categories.orderdown',
			'uses' => 'CategoriesController@reorder',
			'middleware' => 'can:edit.state orders',
		])->where('id', '[0-9]+');
		$router->post('/saveorder', [
			'as'   => 'admin.orders.categories.saveorder',
			'uses' => 'CategoriesController@saveorder',
			'middleware' => 'can:edit.state orders',
		]);
		$router->post('/cancel', [
			'as' => 'admin.orders.categories.cancel',
			'uses' => 'CategoriesController@cancel',
		]);
	});

	// Products
	$router->group(['prefix' => 'products'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.orders.products',
			'uses' => 'ProductsController@index',
			'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'admin.orders.products.create',
			'uses' => 'ProductsController@create',
			'middleware' => 'can:create orders',
		]);
		$router->post('/store', [
			'as' => 'admin.orders.products.store',
			'uses' => 'ProductsController@store',
			'middleware' => 'can:create orders|edit orders',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.orders.products.edit',
			'uses' => 'ProductsController@edit',
			'middleware' => 'can:edit orders',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.orders.products.delete',
			'uses' => 'ProductsController@delete',
			'middleware' => 'can:delete orders',
		]);
		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'admin.orders.products.orderup',
			'uses' => 'ProductsController@reorder',
			'middleware' => 'can:edit.state orders',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'admin.orders.products.orderdown',
			'uses' => 'ProductsController@reorder',
			'middleware' => 'can:edit.state orders',
		])->where('id', '[0-9]+');
		$router->post('/saveorder', [
			'as'   => 'admin.orders.products.saveorder',
			'uses' => 'ProductsController@saveorder',
			'middleware' => 'can:edit.state orders',
		]);
		$router->post('/cancel', [
			'as' => 'admin.orders.products.cancel',
			'uses' => 'ProductsController@cancel',
		]);
	});

	// Approvers
	$router->group(['prefix' => 'approvers'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.orders.approvers',
			'uses' => 'ApproversController@index',
			//'middleware' => 'can:manage orders',
		]);
		$router->get('/create', [
			'as' => 'admin.orders.approvers.create',
			'uses' => 'ApproversController@create',
			'middleware' => 'can:create orders.approvers',
		]);
		$router->post('/store', [
			'as' => 'admin.orders.approvers.store',
			'uses' => 'ApproversController@store',
			'middleware' => 'can:create orders.approvers|edit orders.approvers',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.orders.approvers.edit',
			'uses' => 'ApproversController@edit',
			'middleware' => 'can:edit orders.approvers',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.orders.approvers.delete',
			'uses' => 'ApproversController@delete',
			'middleware' => 'can:delete orders.approvers',
		]);
		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'admin.orders.approvers.orderup',
			'uses' => 'ApproversController@reorder',
			'middleware' => 'can:edit.state orders',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'admin.orders.approvers.orderdown',
			'uses' => 'ApproversController@reorder',
			'middleware' => 'can:edit.state orders',
		])->where('id', '[0-9]+');
		$router->post('/saveorder', [
			'as'   => 'admin.orders.approvers.saveorder',
			'uses' => 'ApproversController@saveorder',
			'middleware' => 'can:edit.state orders',
		]);
		$router->post('/cancel', [
			'as' => 'admin.orders.approvers.cancel',
			'uses' => 'ApproversController@cancel',
		]);
	});

	// Orders
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.orders.index',
		'uses' => 'OrdersController@index',
		'middleware' => 'can:manage orders',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.orders.cancel',
		'uses' => 'OrdersController@cancel',
	]);
	$router->match(['get', 'post'], '/stats', [
		'as'   => 'admin.orders.stats',
		'uses' => 'OrdersController@stats',
	]);
	$router->get('create', [
		'as' => 'admin.orders.create',
		'uses' => 'OrdersController@create',
		'middleware' => 'can:create orders',
	]);
	$router->post('store', [
		'as' => 'admin.orders.store',
		'uses' => 'OrdersController@store',
		'middleware' => 'can:create orders|edit orders',
	]);
	$router->get('{id}', [
		'as' => 'admin.orders.edit',
		'uses' => 'OrdersController@edit',
		'middleware' => 'can:edit orders',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.orders.delete',
		'uses' => 'OrdersController@delete',
		'middleware' => 'can:delete orders',
	]);
});
