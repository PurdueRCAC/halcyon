<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'search'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.search.index',
		'uses' => 'SearchController@index',
	]);
});
