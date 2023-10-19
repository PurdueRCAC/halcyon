<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'software', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.software.index',
		'uses' => 'ApplicationsController@index',
	]);
	$router->post('/', [
		'as' => 'api.software.create',
		'uses' => 'ApplicationsController@create',
		'middleware' => ['can:create software'],
	]);
	$router->get('{id}', [
		'as'   => 'api.software.read',
		'uses' => 'ApplicationsController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.software.update',
		'uses' => 'ApplicationsController@update',
		'middleware' => ['can:edit software'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.software.delete',
		'uses' => 'ApplicationsController@delete',
		'middleware' => ['can:delete software'],
	])->where('id', '[0-9]+');

	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.software.types',
			'uses' => 'TypesController@index',
		]);
		$router->post('/', [
			'as'   => 'api.software.types.create',
			'uses' => 'TypesController@create',
			'middleware' => ['can:create software'],
		]);
		$router->get('{id}', [
			'as'   => 'api.software.types.read',
			'uses' => 'TypesController@edit',
			'middleware' => ['can:edit software'],
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as'   => 'api.software.types.update',
			'uses' => 'TypesController@update',
			'middleware' => ['can:edit software'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as'   => 'api.software.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => ['can:delete software'],
		])->where('id', '[0-9]+');
	});
});
