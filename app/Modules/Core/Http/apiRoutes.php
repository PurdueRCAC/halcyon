<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('/', [
	'as'   => 'api.core.index',
	'uses' => 'DocsController@index',
	//'middleware' => 'auth:api',
]);
