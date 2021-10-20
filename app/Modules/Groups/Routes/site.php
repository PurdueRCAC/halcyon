<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'groups'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.groups.index',
		'uses' => 'GroupsController@index',
		'middleware' => 'can:manage groups',
	]);
	$router->get('/{id}', [
		'as' => 'site.groups.show',
		'uses' => 'GroupsController@show',
		'middleware' => 'can:view groups',
	])->where('id', '[0-9]+');
	$router->post('/export', [
		'as' => 'site.groups.export',
		'uses' => 'GroupsController@export',
	]);
});
