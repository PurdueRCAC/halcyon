<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'resources', 'middleware' => 'can:manage resources'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.resources.index',
		'uses' => 'ResourcesController@index',
		//'middleware' => 'can:manage resources',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.resources.cancel',
		'uses' => 'ResourcesController@cancel',
	]);
	$router->get('/create', [
		'as'   => 'admin.resources.create',
		'uses' => 'ResourcesController@create',
		'middleware' => 'can:create resources',
	]);
	$router->post('/store', [
		'as'   => 'admin.resources.store',
		'uses' => 'ResourcesController@store',
		'middleware' => 'can:create resources|edit resources',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.resources.edit',
		'uses' => 'ResourcesController@edit',
		'middleware' => 'can:edit resources',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.resources.delete',
		'uses' => 'ResourcesController@delete',
		'middleware' => 'can:delete resources',
	]);
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.resources.restore',
		'uses' => 'ResourcesController@restore',
		'middleware' => 'can:delete resources',
	]);

	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.resources.types',
			'uses' => 'TypesController@index',
			'middleware' => 'can:manage resources.types',
		]);
		$router->get('create', [
			'as' => 'admin.resources.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create resources.types',
		]);
		$router->post('store', [
			'as' => 'admin.resources.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create resources.types|edit resources.types',
		]);
		$router->get('{id}', [
			'as' => 'admin.resources.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit resources.types',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.resources.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete resources.types',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.resources.types.cancel',
			'uses' => 'TypesController@cancel',
		]);
	});

	$router->group(['prefix' => 'batchsystems'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.resources.batchsystems',
			'uses' => 'BatchsystemsController@index',
			'middleware' => 'can:manage resources.batchsystems',
		]);
		$router->get('create', [
			'as' => 'admin.resources.batchsystems.create',
			'uses' => 'BatchsystemsController@create',
			'middleware' => 'can:create resources.batchsystems',
		]);
		$router->post('store', [
			'as' => 'admin.resources.batchsystems.store',
			'uses' => 'BatchsystemsController@store',
			'middleware' => 'can:create resources.batchsystems|edit resources.batchsystems',
		]);
		$router->get('{id}', [
			'as' => 'admin.resources.batchsystems.edit',
			'uses' => 'BatchsystemsController@edit',
			'middleware' => 'can:edit resources.batchsystems',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.resources.batchsystems.delete',
			'uses' => 'BatchsystemsController@delete',
			'middleware' => 'can:delete resources.batchsystems',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.resources.batchsystems.cancel',
			'uses' => 'BatchsystemsController@cancel',
		]);
	});

	$router->group(['prefix' => 'subresources'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.resources.subresources',
			'uses' => 'SubresourcesController@index',
			'middleware' => 'can:manage resources.subresources',
		]);
		$router->get('create', [
			'as' => 'admin.resources.subresources.create',
			'uses' => 'SubresourcesController@create',
			'middleware' => 'can:create resources.subresources',
		]);
		$router->post('store', [
			'as' => 'admin.resources.subresources.store',
			'uses' => 'SubresourcesController@store',
			'middleware' => 'can:create resources.subresources|edit resources.subresources',
		]);
		$router->get('{id}', [
			'as' => 'admin.resources.subresources.edit',
			'uses' => 'SubresourcesController@edit',
			'middleware' => 'can:edit resources.subresources',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/restore/{id?}', [
			'as'   => 'admin.resources.subresources.restore',
			'uses' => 'SubresourcesController@restore',
			'middleware' => 'can:delete resources',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.resources.subresources.delete',
			'uses' => 'SubresourcesController@delete',
			'middleware' => 'can:delete resources.subresources',
		]);
		$router->match(['get', 'post'], '/start/{id?}', [
			'as'   => 'admin.resources.subresources.start',
			'uses' => 'SubresourcesController@start',
			'middleware' => 'can:edit.state resources.subresources',
		]);
		$router->match(['get', 'post'], '/stop/{id?}', [
			'as'   => 'admin.resources.subresources.stop',
			'uses' => 'SubresourcesController@stop',
			'middleware' => 'can:edit.state resources.subresources',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.resources.subresources.cancel',
			'uses' => 'SubresourcesController@cancel',
		]);
	});
});
