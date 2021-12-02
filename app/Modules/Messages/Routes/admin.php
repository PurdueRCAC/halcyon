<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'messages'], function (Router $router)
{
	// Types
	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.messages.types',
			'uses' => 'TypesController@index',
			'middleware' => 'can:manage messages',
		]);
		$router->get('/create', [
			'as' => 'admin.messages.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create messages.types',
		]);
		$router->post('/store', [
			'as' => 'admin.messages.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create messages.types|edit messages.types',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.messages.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit messages.types',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.messages.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete messages.types',
		]);
		$router->post('/cancel', [
			'as' => 'admin.messages.types.cancel',
			'uses' => 'TypesController@cancel',
		]);
	});

	$router->match(['get', 'post'], '/logs', [
		'as'   => 'admin.messages.logs',
		'uses' => 'MessagesController@logs',
		'middleware' => 'can:manage messages',
	]);

	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.messages.index',
		'uses' => 'MessagesController@index',
		'middleware' => 'can:manage messages',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.messages.cancel',
		'uses' => 'MessagesController@cancel',
	]);
	$router->match(['get', 'post'], '/rerun', [
		'as'   => 'admin.messages.rerun',
		'uses' => 'MessagesController@rerun',
	]);
	$router->get('/create', [
		'as' => 'admin.messages.create',
		'uses' => 'MessagesController@create',
		'middleware' => 'can:create messages',
	]);
	$router->post('/store', [
		'as' => 'admin.messages.store',
		'uses' => 'MessagesController@store',
		'middleware' => 'can:create messages|edit messages',
	]);
	$router->get('/edit/{id}', [
		'as' => 'admin.messages.edit',
		'uses' => 'MessagesController@edit',
		'middleware' => 'can:edit messages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.messages.delete',
		'uses' => 'MessagesController@delete',
		'middleware' => 'can:delete messages',
	])->where('id', '[0-9]+');
});
