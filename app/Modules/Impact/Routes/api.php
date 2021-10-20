<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'impact'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.impact.index',
		'uses' => 'ImpactController@index',
		'middleware' => 'auth:api',
	]);
});
