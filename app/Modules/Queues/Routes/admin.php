<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'queues'], function (Router $router)
{
	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.queues.types',
			'uses' => 'TypesController@index',
			'middleware' => 'can:manage queues.types',
		]);
		$router->get('create', [
			'as' => 'admin.queues.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create queues.types',
		]);
		$router->post('store', [
			'as' => 'admin.queues.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create queues.types|edit queues.types',
		]);
		$router->get('{id}', [
			'as' => 'admin.queues.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit queues.types',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.queues.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete queues.types',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.queues.types.cancel',
			'uses' => 'TypesController@cancel',
		]);
	});

	$router->group(['prefix' => 'schedulers'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.queues.schedulers',
			'uses' => 'SchedulersController@index',
			'middleware' => 'can:manage queues.schedulers',
		]);
		$router->get('create', [
			'as' => 'admin.queues.schedulers.create',
			'uses' => 'SchedulersController@create',
			'middleware' => 'can:create queues.schedulers',
		]);
		$router->post('store', [
			'as' => 'admin.queues.schedulers.store',
			'uses' => 'SchedulersController@store',
			'middleware' => 'can:create queues.schedulers|edit queues.schedulers',
		]);
		$router->get('{id}', [
			'as' => 'admin.queues.schedulers.edit',
			'uses' => 'SchedulersController@edit',
			'middleware' => 'can:edit queues.schedulers',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.queues.schedulers.delete',
			'uses' => 'SchedulersController@delete',
			'middleware' => 'can:delete queues.schedulers',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.queues.schedulers.cancel',
			'uses' => 'SchedulersController@cancel',
		]);
	});

	$router->group(['prefix' => 'schedulerpolicies'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as' => 'admin.queues.schedulerpolicies',
			'uses' => 'SchedulerPoliciesController@index',
			'middleware' => 'can:manage queues.schedulerpolicies',
		]);
		$router->get('create', [
			'as' => 'admin.queues.schedulerpolicies.create',
			'uses' => 'SchedulerPoliciesController@create',
			'middleware' => 'can:create queues.schedulerpolicies',
		]);
		$router->post('store', [
			'as' => 'admin.queues.schedulerpolicies.store',
			'uses' => 'SchedulerPoliciesController@store',
			'middleware' => 'can:create queues.schedulerpolicies|edit queues.schedulerpolicies',
		]);
		$router->get('{id}', [
			'as' => 'admin.queues.schedulerpolicies.edit',
			'uses' => 'SchedulerPoliciesController@edit',
			'middleware' => 'can:edit queues.schedulerpolicies',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.queues.schedulerpolicies.delete',
			'uses' => 'SchedulerPoliciesController@delete',
			'middleware' => 'can:delete queues.schedulerpolicies',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.queues.schedulerpolicies.cancel',
			'uses' => 'SchedulerPoliciesController@cancel',
		]);
	});

	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.queues.index',
		'uses' => 'QueuesController@index',
		'middleware' => 'can:manage queues',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.queues.cancel',
		'uses' => 'QueuesController@cancel',
	]);
	$router->get('/create', [
		'as'   => 'admin.queues.create',
		'uses' => 'QueuesController@create',
		'middleware' => 'can:create queues',
	]);
	$router->post('/store', [
		'as'   => 'admin.queues.store',
		'uses' => 'QueuesController@store',
		'middleware' => 'can:create queues|edit queues',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.queues.edit',
		'uses' => 'QueuesController@edit',
		'middleware' => 'can:edit queues',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/enable/{id?}', [
		'as'   => 'admin.queues.enable',
		'uses' => 'QueuesController@state',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/disable/{id?}', [
		'as'   => 'admin.queues.disable',
		'uses' => 'QueuesController@state',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/start/{id?}', [
		'as'   => 'admin.queues.start',
		'uses' => 'QueuesController@scheduling',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/stop/{id?}', [
		'as'   => 'admin.queues.stop',
		'uses' => 'QueuesController@scheduling',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');

	$router->match(['get', 'post'], '/startall/{id}', [
		'as'   => 'admin.queues.startall',
		'uses' => 'QueuesController@allscheduling',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/stopall/{id}', [
		'as'   => 'admin.queues.stopall',
		'uses' => 'QueuesController@allscheduling',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');

	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.queues.restore',
		'uses' => 'QueuesController@restore',
		'middleware' => 'can:edit.state queues',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.queues.delete',
		'uses' => 'QueuesController@delete',
		'middleware' => 'can:delete queues',
	]);
});
