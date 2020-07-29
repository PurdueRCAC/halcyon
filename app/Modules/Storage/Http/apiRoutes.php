<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'storage', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->group(['prefix' => 'notifications'], function (Router $router)
	{
		$router->group(['prefix' => 'types', 'middleware' => 'can:manage storage'], function (Router $router)
		{
			$router->get('/', [
				'as' => 'api.storage.notifications.types',
				'uses' => 'NotificationTypesController@index',
			]);
			$router->post('/', [
				'as' => 'api.storage.notifications.types.create',
				'uses' => 'NotificationTypesController@create',
			]);
			$router->get('{id}', [
				'as' => 'api.storage.notifications.types.read',
				'uses' => 'NotificationTypesController@read',
			])->where('id', '[0-9]+');
			$router->put('{id}', [
				'as' => 'api.storage.notifications.types.update',
				'uses' => 'NotificationTypesController@update',
			])->where('id', '[0-9]+');
			$router->delete('{id}', [
				'as' => 'api.storage.notifications.types.delete',
				'uses' => 'NotificationTypesController@delete',
			])->where('id', '[0-9]+');
		});

		$router->get('/', [
			'as' => 'api.storage.notifications',
			'uses' => 'NotificationsController@index',
			'middleware' => 'can:manage storage.notifications',
		]);
		$router->post('/', [
			'as' => 'api.storage.notifications.create',
			'uses' => 'NotificationsController@create',
			'middleware' => 'can:create storage.notifications',
		]);
		$router->get('{id}', [
			'as' => 'api.storage.notifications.read',
			'uses' => 'NotificationsController@read',
			'middleware' => 'can:create storage.notifications',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.storage.notifications.update',
			'uses' => 'NotificationsController@update',
			'middleware' => 'can:edit storage.notifications',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.storage.notifications.delete',
			'uses' => 'NotificationsController@delete',
			'middleware' => 'can:delete storage.notifications',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'purchases'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.storage.purchases',
			'uses' => 'PurchasesController@index',
		]);
		$router->post('/', [
			'as' => 'api.storage.purchases.create',
			'uses' => 'PurchasesController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.storage.purchases.read',
			'uses' => 'PurchasesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.storage.purchases.update',
			'uses' => 'PurchasesController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.storage.purchases.delete',
			'uses' => 'PurchasesController@delete',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'loans'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.storage.loans',
			'uses' => 'LoansController@index',
		]);
		$router->post('/', [
			'as' => 'api.storage.loans.create',
			'uses' => 'LoansController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.storage.loans.read',
			'uses' => 'LoansController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.storage.loans.update',
			'uses' => 'LoansController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.storage.loans.delete',
			'uses' => 'LoansController@delete',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'usage'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.storage.usage',
			'uses' => 'UsageController@index',
		]);
		$router->post('/', [
			'as' => 'api.storage.usage.create',
			'uses' => 'UsageController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.storage.usage.read',
			'uses' => 'UsageController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.storage.usage.update',
			'uses' => 'UsageController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.storage.usage.delete',
			'uses' => 'UsageController@delete',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'directories'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.storage.directories',
			'uses' => 'DirectoriesController@index',
		]);
		$router->post('/', [
			'as' => 'api.storage.directories.create',
			'uses' => 'DirectoriesController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.storage.directories.read',
			'uses' => 'DirectoriesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.storage.directories.update',
			'uses' => 'DirectoriesController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.storage.directories.delete',
			'uses' => 'DirectoriesController@delete',
		])->where('id', '[0-9]+');
	});

	$router->get('quotas', [
		'as' => 'api.storage.quotas',
		'uses' => 'QuotasController@index',
	]);

	$router->get('/', [
		'as' => 'api.storage.index',
		'uses' => 'StorageController@index',
	]);
	$router->post('/', [
		'as' => 'api.storage.create',
		'uses' => 'StorageController@create',
		'middleware' => 'can:create storage',
	]);
	$router->get('{id}', [
		'as' => 'api.storage.read',
		'uses' => 'StorageController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.storage.update',
		'uses' => 'StorageController@update',
		'middleware' => 'can:edit storage',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.storage.delete',
		'uses' => 'StorageController@delete',
		'middleware' => 'can:delete storage',
	])->where('id', '[0-9]+');
});
