<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('info', [
	'as'   => 'admin.core.sysinfo',
	'uses' => 'InfoController@index',
	'middleware' => 'can:admin',
]);

$router->get('styleguide', [
	'as'   => 'admin.core.styles',
	'uses' => 'InfoController@styles',
	'middleware' => 'can:admin',
]);

/** @var Router $router */
$router->group(['prefix' => 'modules', 'middleware' => 'can:admin'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.modules.index',
		'uses' => 'ModulesController@index',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.modules.cancel',
		'uses' => 'ModulesController@cancel',
	]);
	$router->get('/create', [
		'as' => 'admin.modules.create',
		'uses' => 'ModulesController@create',
	]);
	$router->post('/store', [
		'as' => 'admin.modules.store',
		'uses' => 'ModulesController@store',
	]);
	$router->get('/edit/{id}', [
		'as' => 'admin.modules.edit',
		'uses' => 'ModulesController@edit',
	]);
	$router->match(['get', 'post'], '/enable/{id?}', [
		'as'   => 'admin.modules.enable',
		'uses' => 'ModulesController@state',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/disable/{id?}', [
		'as'   => 'admin.modules.disable',
		'uses' => 'ModulesController@state',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.modules.delete',
		'uses' => 'ModulesController@delete',
	]);
});
