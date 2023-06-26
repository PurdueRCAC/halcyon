<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'impact', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.impact.index',
		'uses' => 'ImpactController@index',
	]);
});
