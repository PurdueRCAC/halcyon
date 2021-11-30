<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'themes', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.themes.index',
		'uses' => 'ThemesController@index',
	]);
	$router->post('/', [
		'as' => 'api.themes.create',
		'uses' => 'ThemesController@create',
		'middleware' => 'can:create themes',
	]);
	$router->get('{id}', [
		'as'   => 'api.themes.read',
		'uses' => 'ThemesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.themes.update',
		'uses' => 'ThemesController@update',
		'middleware' => 'can:edit themes',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.themes.delete',
		'uses' => 'ThemesController@delete',
		'middleware' => 'can:delete themes',
	])->where('id', '[0-9]+');
});
