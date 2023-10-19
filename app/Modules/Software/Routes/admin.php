<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'software'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.software.index',
		'uses' => 'ApplicationsController@index',
		'middleware' => 'can:manage software',
	]);
	$router->get('/create', [
		'as'   => 'admin.software.create',
		'uses' => 'ApplicationsController@create',
		'middleware' => 'can:create software',
	]);
	$router->post('/store', [
		'as'   => 'admin.software.store',
		'uses' => 'ApplicationsController@store',
		'middleware' => 'can:create software|edit software',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.software.edit',
		'uses' => 'ApplicationsController@edit',
		'middleware' => 'can:edit software',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.software.delete',
		'uses' => 'ApplicationsController@delete',
		'middleware' => 'can:delete software',
	]);
	$router->match(['get', 'post'], '/deletefile/{id}', [
		'as'   => 'admin.software.deletefile',
		'uses' => 'ApplicationsController@deletefile',
		'middleware' => 'can:edit software',
	]);
	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'admin.software.publish',
		'uses' => 'ApplicationsController@state',
		'middleware' => 'can:edit.state software',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'admin.software.unpublish',
		'uses' => 'ApplicationsController@state',
		'middleware' => 'can:edit.state software',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.software.restore',
		'uses' => 'ApplicationsController@restore',
		'middleware' => 'can:edit.state software',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.software.types.cancel',
		'uses' => 'TypesController@cancel',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.software.cancel',
		'uses' => 'ApplicationsController@cancel',
	]);

	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.software.types',
			'uses' => 'TypesController@index',
		]);
		$router->get('/create', [
			'as'   => 'admin.software.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create software',
		]);
		$router->post('/store', [
			'as'   => 'admin.software.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create software|edit software',
		]);
		$router->get('/{id}', [
			'as'   => 'admin.software.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit software',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.software.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete software',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as'   => 'admin.software.types.cancel',
			'uses' => 'TypesController@cancel',
		]);
	});
});
