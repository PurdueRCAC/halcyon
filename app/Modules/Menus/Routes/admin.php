<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'menus'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.menus.index',
		'uses' => 'MenusController@index',
		'middleware' => 'can:manage menus',
	]);
	$router->get('/create', [
		'as'   => 'admin.menus.create',
		'uses' => 'MenusController@create',
		'middleware' => 'can:create menus',
	]);
	$router->post('/store', [
		'as'   => 'admin.menus.store',
		'uses' => 'MenusController@store',
		'middleware' => 'can:create menus|edit menus',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.menus.edit',
		'uses' => 'MenusController@edit',
		'middleware' => 'can:edit menus',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.menus.delete',
		'uses' => 'MenusController@delete',
		'middleware' => 'can:delete menus',
	]);
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.menus.restore',
		'uses' => 'MenusController@restore',
		'middleware' => 'can:edit.state menus',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.menus.cancel',
		'uses' => 'MenusController@cancel',
	]);
	$router->get('/rebuild/{menutype}', [
		'as' => 'admin.menus.rebuild',
		'uses' => 'MenusController@rebuild',
	]);

	$router->group(['prefix' => 'items'], function (Router $router)
	{
		$router->match(['get', 'post'], '/publish/{id?}', [
			'as'   => 'admin.menus.items.publish',
			'uses' => 'ItemsController@state',
			'middleware' => 'can:edit.state menus',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/unpublish/{id?}', [
			'as'   => 'admin.menus.items.unpublish',
			'uses' => 'ItemsController@state',
			'middleware' => 'can:edit.state menus',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/restore/{id?}', [
			'as'   => 'admin.menus.items.restore',
			'uses' => 'ItemsController@restore',
			'middleware' => 'can:edit.state menus',
		])->where('id', '[0-9]+');

		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'admin.menus.items.orderup',
			'uses' => 'ItemsController@reorder',
			'middleware' => 'can:edit.state menus',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'admin.menus.items.orderdown',
			'uses' => 'ItemsController@reorder',
			'middleware' => 'can:edit.state menus',
		])->where('id', '[0-9]+');

		$router->get('/{id}/setdefault', [
			'as'   => 'admin.menus.setdefault',
			'uses' => 'ItemsController@setdefault',
			'middleware' => 'can:edit menus',
		])->where('id', '[0-9]+');
		$router->get('/create', [
			'as'   => 'admin.menus.items.create',
			'uses' => 'ItemsController@create',
			'middleware' => 'can:create menus',
		]);
		$router->post('/store', [
			'as'   => 'admin.menus.items.store',
			'uses' => 'ItemsController@store',
			'middleware' => 'can:create menus|edit menus',
		]);
		$router->get('/{id}', [
			'as'   => 'admin.menus.items.edit',
			'uses' => 'ItemsController@edit',
			'middleware' => 'can:edit menus',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.menus.items.delete',
			'uses' => 'ItemsController@delete',
			'middleware' => 'can:delete menus',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.menus.items.cancel',
			'uses' => 'ItemsController@cancel',
		]);
		$router->get('/types', [
			'as' => 'admin.menus.items.types',
			'uses' => 'ItemsController@types',
		]);
		$router->match(['get', 'post'], '/{menutype?}', [
			'as'   => 'admin.menus.items',
			'uses' => 'ItemsController@index',
			'middleware' => 'can:edit menus',
		]);
	});
});
