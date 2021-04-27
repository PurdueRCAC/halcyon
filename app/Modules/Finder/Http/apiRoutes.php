<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'finder'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@index',
	]);
	$router->post('/', [
		'as' => 'api.finder.create',
		'uses' => 'FinderController@create',
		'middleware' => ['auth:api', 'can:create finder'],
	]);
	$router->get('{id}', [
		'as' => 'api.finder.read',
		'uses' => 'FinderController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.finder.update',
		'uses' => 'FinderController@update',
		'middleware' => ['auth:api', 'can:edit finder'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.finder.delete',
		'uses' => 'FinderController@delete',
		'middleware' => ['auth:api', 'can:delete finder'],
	])->where('id', '[0-9]+');

	$router->post('/sendmail', [
		'as' => 'api.finder.sendmail',
		'uses' => 'FinderController@sendmail',
		'middleware' => ['auth:api', 'can:manage finder'],
	]);

	$router->get('/servicelist', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@servicelist',
	]);

	$router->get('/facettree', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@facettree',
	]);

	$router->get('/settings', [
		'as'   => 'api.finder.index',
		'uses' => 'FinderController@settings',
	]);
});
