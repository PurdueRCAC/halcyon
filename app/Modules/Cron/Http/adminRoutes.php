<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'cron', 'middleware' => 'can:manage cron'], function (Router $router)
{
	$router->get('/', [
		'as' => 'admin.cron.index',
		'uses' => 'JobsController@index',
	]);
	$router->get('create', [
		'as' => 'admin.cron.create',
		'uses' => 'JobsController@create',
		'middleware' => 'can:create cron',
	]);
	$router->post('store', [
		'as' => 'admin.cron.store',
		'uses' => 'JobsController@store',
		'middleware' => 'can:create cron,edit cron',
	]);
	$router->get('{id}', [
		'as' => 'admin.cron.edit',
		'uses' => 'JobsController@edit',
		'middleware' => 'can:edit cron',
	]);
	$router->match(['get', 'post'], 'publish/{id?}', [
		'as' => 'admin.cron.publish',
		'uses' => 'JobsController@state',
		'middleware' => 'can:edit.state cron',
	]);
	$router->match(['get', 'post'], 'unpublish/{id?}', [
		'as' => 'admin.cron.unpublish',
		'uses' => 'JobsController@state',
		'middleware' => 'can:edit.state cron',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.cron.delete',
		'uses' => 'JobsController@delete',
		'middleware' => 'can:delete cron',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.cron.cancel',
		'uses' => 'JobsController@cancel',
	]);
});
