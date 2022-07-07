<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'tags'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as' => 'admin.tags.index',
		'uses' => 'TagsController@index',
		'middleware' => 'can:manage tags',
	]);
	$router->get('create', [
		'as' => 'admin.tags.create',
		'uses' => 'TagsController@create',
		'middleware' => 'can:create tags',
	]);
	$router->post('store', [
		'as' => 'admin.tags.store',
		'uses' => 'TagsController@store',
		'middleware' => 'can:create tags|edit tags',
	]);
	$router->get('edit/{id}', [
		'as' => 'admin.tags.edit',
		'uses' => 'TagsController@edit',
		'middleware' => 'can:edit tags',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.tags.delete',
		'uses' => 'TagsController@delete',
		'middleware' => 'can:delete tags',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.tags.cancel',
		'uses' => 'TagsController@cancel',
	]);
});
