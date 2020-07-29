<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'themes'], function (Router $router)
{
	$router->get('/', [
		'as' => 'admin.themes.index',
		'uses' => 'ThemesController@index',
		'middleware' => 'can:manage themes',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.themes.cancel',
		'uses' => 'ThemesController@cancel',
	]);
	$router->post('store', [
		'as' => 'admin.themes.store',
		'uses' => 'ThemesController@store',
		'middleware' => 'can:create themes,edit themes',
	]);
	$router->get('{id}', [
		'as' => 'admin.themes.edit',
		'uses' => 'ThemesController@edit',
		'middleware' => 'can:edit themes',
	]);

	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.themes.delete',
		'uses' => 'ThemesController@delete',
		'middleware' => 'can:delete themes',
	]);
});
