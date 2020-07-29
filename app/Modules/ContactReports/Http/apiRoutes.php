<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'contactreports'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.contactreports.index',
		'uses' => 'ReportsController@index',
	]);
	$router->post('/', [
		'as' => 'api.contactreports.create',
		'uses' => 'ReportsController@create',
		'middleware' => 'auth:api',
	]);
	$router->get('{id}', [
		'as'   => 'api.contactreports.read',
		'uses' => 'ReportsController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.contactreports.update',
		'uses' => 'ReportsController@update',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.contactreports.delete',
		'uses' => 'ReportsController@delete',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');

	// Comments
	$router->group(['prefix' => '/comments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.contactreports.comments',
			'uses' => 'CommentsController@index',
		]);
		$router->post('/', [
			'as' => 'api.contactreports.comments.create',
			'uses' => 'CommentsController@create',
			'middleware' => 'auth:api',
		]);
		$router->get('{comment}', [
			'as' => 'api.contactreports.comments.read',
			'uses' => 'CommentsController@read',
		])->where('comment', '[0-9]+');
		$router->put('{comment}', [
			'as' => 'api.contactreports.comments.update',
			'uses' => 'CommentsController@update',
			'middleware' => 'auth:api',
		])->where('comment', '[0-9]+');
		$router->delete('{comment}', [
			'as' => 'api.contactreports.comments.delete',
			'uses' => 'CommentsController@delete',
			'middleware' => 'auth:api',
		])->where('comment', '[0-9]+');
	});
});
