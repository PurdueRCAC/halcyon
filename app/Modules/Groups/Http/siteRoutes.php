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
	]);
});
