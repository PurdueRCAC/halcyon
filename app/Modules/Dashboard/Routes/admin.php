<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('/', [
	'as' => 'admin.dashboard.index',
	'uses' => 'DashboardController@index',
]);

$router->group(['prefix' => 'dashboard'], function (Router $router)
{
	$router->get('/', [
		'as' => 'admin.dashboard.index',
		'uses' => 'DashboardController@index',
	]);
});
