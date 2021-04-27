<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'finder', 'middleware' => 'can:manage finder'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as' => 'admin.finder.index',
		'uses' => 'FinderController@index',
	]);
	$router->get('create', [
		'as' => 'admin.finder.create',
		'uses' => 'FinderController@create',
		'middleware' => 'can:create finder',
	]);
	$router->post('store', [
		'as' => 'admin.finder.store',
		'uses' => 'FinderController@store',
		'middleware' => 'can:create finder,edit finder',
	]);
	$router->get('edit/{id}', [
		'as' => 'admin.finder.edit',
		'uses' => 'FinderController@edit',
		'middleware' => 'can:edit finder',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.finder.delete',
		'uses' => 'FinderController@delete',
		'middleware' => 'can:delete finder',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.finder.cancel',
		'uses' => 'FinderController@cancel',
	]);
});
