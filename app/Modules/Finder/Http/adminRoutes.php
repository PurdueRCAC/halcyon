<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'finder', 'middleware' => 'can:manage finder'], function (Router $router)
{
	$router->group(['prefix' => 'services'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.finder.services',
			'uses' => 'ServicesController@index',
			'middleware' => 'can:manage finder',
		]);
		$router->get('/create', [
			'as' => 'admin.finder.services.create',
			'uses' => 'ServicesController@create',
			'middleware' => 'can:create finder',
		]);
		$router->post('/store', [
			'as' => 'admin.finder.services.store',
			'uses' => 'ServicesController@store',
			'middleware' => 'can:create finder,edit finder',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.finder.services.edit',
			'uses' => 'ServicesController@edit',
			'middleware' => 'can:edit finder',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.finder.services.delete',
			'uses' => 'ServicesController@delete',
			'middleware' => 'can:delete finder',
		]);
		$router->post('/cancel', [
			'as' => 'admin.finder.services.cancel',
			'uses' => 'ServicesController@cancel',
		]);

		$router->match(['get', 'post'], '/publish/{id?}', [
			'as'   => 'admin.finder.services.publish',
			'uses' => 'ServicesController@state',
			'middleware' => 'can:edit.state finder',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/unpublish/{id?}', [
			'as'   => 'admin.finder.services.unpublish',
			'uses' => 'ServicesController@state',
			'middleware' => 'can:edit.state finder',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'fields'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.finder.fields',
			'uses' => 'FieldsController@index',
			'middleware' => 'can:manage finder',
		]);
		$router->get('/create', [
			'as' => 'admin.finder.fields.create',
			'uses' => 'FieldsController@create',
			'middleware' => 'can:create finder',
		]);
		$router->post('/store', [
			'as' => 'admin.finder.fields.store',
			'uses' => 'FieldsController@store',
			'middleware' => 'can:create finder,edit finder',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.finder.fields.edit',
			'uses' => 'FieldsController@edit',
			'middleware' => 'can:edit finder',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.finder.fields.delete',
			'uses' => 'FieldsController@delete',
			'middleware' => 'can:delete finder',
		]);
		$router->post('/cancel', [
			'as' => 'admin.finder.fields.cancel',
			'uses' => 'FieldsController@cancel',
		]);

		$router->match(['get', 'post'], '/publish/{id?}', [
			'as'   => 'admin.finder.fields.publish',
			'uses' => 'FieldsController@state',
			'middleware' => 'can:edit.state finder',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/unpublish/{id?}', [
			'as'   => 'admin.finder.fields.unpublish',
			'uses' => 'FieldsController@state',
			'middleware' => 'can:edit.state finder',
		])->where('id', '[0-9]+');
	});

	$router->match(['get', 'post'], '/', [
		'as' => 'admin.finder.index',
		'uses' => 'FacetsController@index',
	]);
	$router->get('create', [
		'as' => 'admin.finder.create',
		'uses' => 'FacetsController@create',
		'middleware' => 'can:create finder',
	]);
	$router->post('store', [
		'as' => 'admin.finder.store',
		'uses' => 'FacetsController@store',
		'middleware' => 'can:create finder,edit finder',
	]);
	$router->get('edit/{id}', [
		'as' => 'admin.finder.edit',
		'uses' => 'FacetsController@edit',
		'middleware' => 'can:edit finder',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.finder.delete',
		'uses' => 'FacetsController@delete',
		'middleware' => 'can:delete finder',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.finder.cancel',
		'uses' => 'FacetsController@cancel',
	]);

	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'admin.finder.publish',
		'uses' => 'FacetsController@state',
		'middleware' => 'can:edit.state finder',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'admin.finder.unpublish',
		'uses' => 'FacetsController@state',
		'middleware' => 'can:edit.state finder',
	])->where('id', '[0-9]+');
});
