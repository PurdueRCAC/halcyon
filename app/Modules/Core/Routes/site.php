<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('docs', [
	'as'   => 'site.core.docs',
	'uses' => 'DocsController@index',
	//'middleware' => 'can:admin',
]);

$router->get('captcha', [
	'as'   => 'site.core.captcha',
	'uses' => 'CaptchaController@index',
]);
