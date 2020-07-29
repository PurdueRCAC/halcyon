<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'storage'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.storage.index',
		'uses' => 'StorageController@index',
	]);
	$router->get('/{name}', [
		'as' => 'site.storage.show',
		'uses' => 'StorageController@show',
	]);
});
