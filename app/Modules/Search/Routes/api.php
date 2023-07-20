<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'search'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.knowledge.index',
		'uses' => 'PagesController@index',
	]);
});
