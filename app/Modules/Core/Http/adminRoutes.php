<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('info', [
	'as'   => 'admin.core.sysinfo',
	'uses' => 'InfoController@index',
	'middleware' => 'can:admin',
]);
