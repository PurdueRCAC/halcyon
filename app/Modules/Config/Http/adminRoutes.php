<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'config'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'admin.config',
		'uses' => 'GlobalController@index',
		'middleware' => 'can:manage config',
	]);
	$router->post('store', [
		'as'   => 'admin.config.store',
		'uses' => 'GlobalController@store',
		'middleware' => 'can:manage config',
	]);

	$router->get('module/{module}', [
		'as'   => 'admin.config.module',
		'uses' => 'ModulesController@index',
		//'middleware' => 'can:tag.tags.edit',
	]);
	$router->post('module/{module}/update', [
		'as'   => 'admin.config.module.update',
		'uses' => 'ModulesController@update',
	]);
});
