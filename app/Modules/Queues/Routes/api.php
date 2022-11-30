<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'queues'], function (Router $router)
{
	$router->group(['prefix' => 'types', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.types',
			'uses' => 'TypesController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:manage queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.types.read',
			'uses' => 'TypesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.types.update',
			'uses' => 'TypesController@update',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'qos', 'middleware' => 'auth.ip'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.qos',
			'uses' => 'QosController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.qos.create',
			'uses' => 'QosController@create',
			'middleware' => 'can:manage queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.qos.read',
			'uses' => 'QosController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.qos.update',
			'uses' => 'QosController@update',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.qos.delete',
			'uses' => 'QosController@delete',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'walltimes', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.walltimes',
			'uses' => 'WalltimesController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.walltimes.create',
			'uses' => 'WalltimesController@create',
			'middleware' => 'can:edit queues|edit.own queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.walltimes.read',
			'uses' => 'WalltimesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.walltimes.update',
			'uses' => 'WalltimesController@update',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.walltimes.delete',
			'uses' => 'WalltimesController@delete',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'schedulers', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.schedulers',
			'uses' => 'SchedulersController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.schedulers.create',
			'uses' => 'SchedulersController@create',
			'middleware' => 'can:manage queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.schedulers.read',
			'uses' => 'SchedulersController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.schedulers.update',
			'uses' => 'SchedulersController@update',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.schedulers.delete',
			'uses' => 'SchedulersController@delete',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'schedulerpolicies', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.schedulerpolicies',
			'uses' => 'SchedulerPoliciesController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.schedulerpolicies.create',
			'uses' => 'SchedulerPoliciesController@create',
			'middleware' => 'can:manage queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.schedulerpolicies.read',
			'uses' => 'SchedulerPoliciesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.schedulerpolicies.update',
			'uses' => 'SchedulerPoliciesController@update',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.schedulerpolicies.delete',
			'uses' => 'SchedulerPoliciesController@delete',
			'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'loans', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.loans',
			'uses' => 'LoansController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.loans.create',
			'uses' => 'LoansController@create',
			'middleware' => 'can:edit queues|edit.own queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.loans.read',
			'uses' => 'LoansController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.loans.update',
			'uses' => 'LoansController@update',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.loans.delete',
			'uses' => 'LoansController@delete',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'sizes', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.sizes',
			'uses' => 'SizesController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.sizes.create',
			'uses' => 'SizesController@create',
			'middleware' => 'can:edit queues|edit.own queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.sizes.read',
			'uses' => 'SizesController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.sizes.update',
			'uses' => 'SizesController@update',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.sizes.delete',
			'uses' => 'SizesController@delete',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'requests', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.requests',
			'uses' => 'UserRequestsController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.requests.create',
			'uses' => 'UserRequestsController@create',
			//'middleware' => 'can:manage queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.requests.read',
			'uses' => 'UserRequestsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.requests.update',
			'uses' => 'UserRequestsController@update',
			//'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.requests.delete',
			'uses' => 'UserRequestsController@delete',
			//'middleware' => 'can:manage queues',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'users', 'middleware' => 'auth:api'], function (Router $router)
	{
		$router->get('/', [
			'as' => 'api.queues.users',
			'uses' => 'UsersController@index',
		]);
		$router->post('/', [
			'as' => 'api.queues.users.create',
			'uses' => 'UsersController@create',
			'middleware' => 'can:edit queues|edit.own queues',
		]);
		$router->get('{id}', [
			'as' => 'api.queues.users.read',
			'uses' => 'UsersController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.queues.users.update',
			'uses' => 'UsersController@update',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.queues.users.delete',
			'uses' => 'UsersController@delete',
			'middleware' => 'can:edit queues|edit.own queues',
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as' => 'api.queues.index',
		'uses' => 'QueuesController@index',
	]);
	$router->post('/', [
		'as' => 'api.queues.create',
		'uses' => 'QueuesController@create',
		'middleware' => ['auth:api', 'can:create queues'],
	]);
	$router->get('{id}', [
		'as' => 'api.queues.read',
		'uses' => 'QueuesController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.queues.update',
		'uses' => 'QueuesController@update',
		'middleware' => ['auth:api', 'can:edit queues'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.queues.delete',
		'uses' => 'QueuesController@delete',
		'middleware' => ['auth:api', 'can:delete queues'],
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'allocations', 'middleware' => 'auth.ip'], function (Router $router)
{
	$router->get('/{hostname?}', [
		'as' => 'api.allocations.index',
		'uses' => 'AllocationsController@index',
		'middleware' => 'auth.optional:api',
	])->where('hostname', '[a-z0-9\-\.]+');
	$router->post('/', [
		'as' => 'api.allocations.create',
		'uses' => 'AllocationsController@create',
		'middleware' => 'can:create queues',
	]);
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.allocations.update',
		'uses' => 'AllocationsController@update',
		'middleware' => 'can:edit queues',
	]);
	$router->delete('{id}', [
		'as' => 'api.allocations.delete',
		'uses' => 'AllocationsController@delete',
		'middleware' => 'can:delete queues',
	]);
});
