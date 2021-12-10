<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'courses', 'middleware' => 'can:manage courses'], function (Router $router)
{
	// Members
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.courses.members',
			'uses' => 'MembersController@index',
		]);
		$router->get('/create', [
			'as' => 'admin.courses.members.create',
			'uses' => 'MembersController@create',
			'middleware' => 'can:edit courses|edit.own courses',
		]);
		$router->post('/store', [
			'as' => 'admin.courses.members.store',
			'uses' => 'MembersController@store',
			'middleware' => 'can:edit courses|edit.own courses',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.courses.members.edit',
			'uses' => 'MembersController@edit',
			'middleware' => 'can:edit courses|edit.own courses',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.courses.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => 'can:edit courses|edit.own courses',
		]);
		$router->post('/cancel', [
			'as' => 'admin.courses.members.cancel',
			'uses' => 'MembersController@cancel',
		]);
	});

	$router->match(['get', 'post'], '/', [
		'as' => 'admin.courses.index',
		'uses' => 'AccountsController@index',
	]);
	$router->get('create', [
		'as' => 'admin.courses.create',
		'uses' => 'AccountsController@create',
		'middleware' => 'can:create courses',
	]);
	$router->post('store', [
		'as' => 'admin.courses.store',
		'uses' => 'AccountsController@store',
		'middleware' => 'can:create courses|edit courses',
	]);
	$router->get('/mail', [
		'as'   => 'admin.courses.mail',
		'uses' => 'AccountsController@mail',
		'middleware' => 'can:manage courses',
	]);
	$router->post('/send', [
		'as' => 'admin.courses.send',
		'uses' => 'AccountsController@send',
		'middleware' => 'can:manage courses',
	]);
	$router->get('{id}', [
		'as' => 'admin.courses.edit',
		'uses' => 'AccountsController@edit',
		'middleware' => 'can:edit courses',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.courses.delete',
		'uses' => 'AccountsController@delete',
		'middleware' => 'can:delete courses',
	]);
	$router->get('/sync', [
		'as'   => 'admin.courses.sync',
		'uses' => 'AccountsController@sync',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.courses.cancel',
		'uses' => 'AccountsController@cancel',
	]);
});
