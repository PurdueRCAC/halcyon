<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('/status', [
	'as' => 'site.status.index',
	'uses' => 'StatusController@index',
]);
