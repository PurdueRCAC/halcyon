<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('/finder', [
	'as' => 'site.finder.index',
	'uses' => 'FinderController@index',
]);

$router->get('/storage/solutions', [
	'as' => 'site.storage.solutions',
	'uses' => 'FinderController@index',
]);
