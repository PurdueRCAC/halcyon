<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'groups', 'middleware' => ['auth']], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.groups.index',
		'uses' => 'GroupsController@index',
		'middleware' => 'can:manage groups',
	]);
	$router->get('/create', [
		'as' => 'site.groups.create',
		'uses' => 'GroupsController@create',
		'middleware' => 'can:create groups',
	]);
	$router->get('/{id}', [
		'as' => 'site.groups.show',
		'uses' => 'GroupsController@show',
		'middleware' => 'can:view groups',
	])->where('id', '[0-9]+');
	$router->post('/export', [
		'as' => 'site.groups.export',
		'uses' => 'GroupsController@export',
		//'middleware' => 'can:view groups',
	]);
	$router->post('import', [
		'as' => 'site.groups.import',
		'uses' => 'GroupsController@import',
		'middleware' => 'can:manage groups',
	]);
	$router->post('process', [
		'as' => 'site.groups.process',
		'uses' => 'GroupsController@process',
		'middleware' => 'can:manage groups',
	]);
});
