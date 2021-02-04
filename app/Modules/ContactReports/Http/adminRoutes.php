<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'contactreports', 'middleware' => 'can:manage contactreports'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.contactreports.index',
		'uses' => 'ReportsController@index',
		//'middleware' => 'can:manage contactreports',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.contactreports.cancel',
		'uses' => 'ReportsController@cancel',
	]);
	$router->get('/create', [
		'as' => 'admin.contactreports.create',
		'uses' => 'ReportsController@create',
		'middleware' => 'can:create contactreports',
	]);
	$router->post('/store', [
		'as' => 'admin.contactreports.store',
		'uses' => 'ReportsController@store',
		'middleware' => 'can:create contactreports,edit contactreports',
	]);
	$router->get('/edit/{id}', [
		'as' => 'admin.contactreports.edit',
		'uses' => 'ReportsController@edit',
		'middleware' => 'can:edit contactreports',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.contactreports.delete',
		'uses' => 'ReportsController@delete',
		'middleware' => 'can:delete contactreports',
	]);

	// Comments
	$router->group(['prefix' => '{report}/comments'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.contactreports.comments',
			'uses' => 'CommentsController@index',
			//'middleware' => 'can:manage contactreports',
		]);
		$router->get('/create', [
			'as' => 'admin.contactreports.comments.create',
			'uses' => 'CommentsController@create',
			'middleware' => 'can:create contactreports',
		]);
		$router->post('/store', [
			'as' => 'admin.contactreports.comments.store',
			'uses' => 'CommentsController@store',
			'middleware' => 'can:create contactreports,edit contactreports',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.contactreports.comments.edit',
			'uses' => 'CommentsController@edit',
			'middleware' => 'can:edit contactreports',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.contactreports.comments.delete',
			'uses' => 'CommentsController@delete',
			'middleware' => 'can:delete contactreports',
		]);
		$router->match(['get', 'post'], '/cancel', [
			'as' => 'admin.contactreports.comments.cancel',
			'uses' => 'CommentsController@cancel',
		]);
	});

	// Types
	$router->group(['prefix' => '/types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.contactreports.types',
			'uses' => 'TypesController@index',
		]);
		$router->get('/create', [
			'as' => 'admin.contactreports.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create contactreports',
		]);
		$router->post('/store', [
			'as' => 'admin.contactreports.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create contactreports,edit contactreports',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.contactreports.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit contactreports',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.contactreports.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete contactreports',
		]);
		$router->match(['get', 'post'], '/cancel', [
			'as' => 'admin.contactreports.types.cancel',
			'uses' => 'TypesController@cancel',
		]);
	});
});
