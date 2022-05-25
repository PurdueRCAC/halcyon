<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'mail'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.mailer.index',
		'uses' => 'MessagesController@index',
		'middleware' => 'can:manage mail',
	]);
	$router->get('/create', [
		'as'   => 'admin.mailer.create',
		'uses' => 'MessagesController@create',
		'middleware' => 'can:create mail',
	]);
	/*$router->post('/store', [
		'as'   => 'admin.mailer.store',
		'uses' => 'MessagesController@store',
		'middleware' => 'can:create mail|edit mail',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.mailer.edit',
		'uses' => 'MessagesController@edit',
		'middleware' => 'can:edit mail',
	])->where('id', '[0-9]+');*/
	$router->get('/{id}', [
		'as'   => 'admin.mailer.show',
		'uses' => 'MessagesController@show',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.mailer.delete',
		'uses' => 'MessagesController@delete',
		'middleware' => 'can:delete mail',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.mailer.cancel',
		'uses' => 'MessagesController@cancel',
	]);
	/*$router->get('/create', [
		'as'   => 'admin.mailer.create',
		'uses' => 'MessagesController@create',
	])->where('id', '[0-9]+');*/
	$router->get('/preview/{id}', [
		'as'   => 'admin.mailer.preview',
		'uses' => 'MessagesController@preview',
	])->where('id', '[0-9]+');
	$router->post('/send', [
		'as'   => 'admin.mailer.send',
		'uses' => 'MessagesController@send',
	]);

	$router->group(['prefix' => 'templates'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.mailer.templates',
			'uses' => 'TemplatesController@index',
			'middleware' => 'can:manage mail',
		]);
		$router->get('/create', [
			'as'   => 'admin.mailer.templates.create',
			'uses' => 'TemplatesController@create',
			'middleware' => 'can:create mail',
		]);
		$router->post('/store', [
			'as'   => 'admin.mailer.templates.store',
			'uses' => 'TemplatesController@store',
			'middleware' => 'can:create mail|edit mail',
		]);
		$router->get('/{id}', [
			'as'   => 'admin.mailer.templates.edit',
			'uses' => 'TemplatesController@edit',
			'middleware' => 'can:edit mail',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.mailer.templates.delete',
			'uses' => 'TemplatesController@delete',
			'middleware' => 'can:delete mail',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.mailer.templates.cancel',
			'uses' => 'TemplatesController@cancel',
		]);
	});
});
