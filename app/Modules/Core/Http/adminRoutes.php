<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('info', [
	'as'   => 'admin.core.sysinfo',
	'uses' => 'InfoController@index',
	'middleware' => 'can:admin',
]);

$router->get('styleguide', [
	'as'   => 'admin.core.styles',
	'uses' => 'InfoController@styles',
	'middleware' => 'can:admin',
]);
