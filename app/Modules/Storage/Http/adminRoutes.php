<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'storage'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as' => 'admin.storage.index',
		'uses' => 'StorageController@index',
		'middleware' => 'can:manage storage',
	]);
	$router->get('create', [
		'as' => 'admin.storage.create',
		'uses' => 'StorageController@create',
		'middleware' => 'can:create storage',
	]);
	$router->post('store', [
		'as' => 'admin.storage.store',
		'uses' => 'StorageController@store',
		'middleware' => 'can:create storage|edit storage',
	]);
	$router->get('{id}', [
		'as' => 'admin.storage.edit',
		'uses' => 'StorageController@edit',
		'middleware' => 'can:edit storage',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.storage.delete',
		'uses' => 'StorageController@delete',
		'middleware' => 'can:delete storage',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.storage.cancel',
		'uses' => 'StorageController@cancel',
	]);

	$router->group(['prefix' => 'directories'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.storage.directories',
			'uses' => 'DirectoriesController@index',
			'middleware' => 'can:manage storage',
		]);
		$router->get('create', [
			'as' => 'admin.storage.directories.create',
			'uses' => 'DirectoriesController@create',
			'middleware' => 'can:create storage',
		]);
		$router->post('store', [
			'as' => 'admin.storage.directories.store',
			'uses' => 'DirectoriesController@store',
			'middleware' => 'can:create storage|edit storage',
		]);
		$router->get('{id}', [
			'as' => 'admin.storage.directories.edit',
			'uses' => 'DirectoriesController@edit',
			'middleware' => 'can:edit storage',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.storage.directories.delete',
			'uses' => 'DirectoriesController@delete',
			'middleware' => 'can:delete storage',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.storage.directories.cancel',
			'uses' => 'DirectoriesController@cancel',
		]);
	});

	$router->group(['prefix' => 'types', 'middleware' => 'can:manage storage'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.storage.types',
			'uses' => 'NotificationTypesController@index',
			//'middleware' => 'can:manage storage',
		]);
		$router->get('create', [
			'as' => 'admin.storage.types.create',
			'uses' => 'NotificationTypesController@create',
			//'middleware' => 'can:create storage',
		]);
		$router->post('store', [
			'as' => 'admin.storage.types.store',
			'uses' => 'NotificationTypesController@store',
			//'middleware' => 'can:create storage|edit storage',
		]);
		$router->get('{id}', [
			'as' => 'admin.storage.types.edit',
			'uses' => 'NotificationTypesController@edit',
			//'middleware' => 'can:edit storage',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.storage.types.delete',
			'uses' => 'NotificationTypesController@delete',
			//'middleware' => 'can:delete storage',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.storage.types.cancel',
			'uses' => 'NotificationTypesController@cancel',
		]);
	});
});
