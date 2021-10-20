<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'impact', 'middleware' => 'can:manage impact'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as' => 'admin.impact.index',
		'uses' => 'ImpactController@index',
	]);
});
