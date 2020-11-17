<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'contactreports', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.contactreports.index',
		'uses' => 'ReportsController@index',
	]);
	$router->post('/', [
		'as' => 'api.contactreports.create',
		'uses' => 'ReportsController@create',
		'middleware' => 'can:create contactreports',
	]);
	$router->get('{id}', [
		'as'   => 'api.contactreports.read',
		'uses' => 'ReportsController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.contactreports.update',
		'uses' => 'ReportsController@update',
		'middleware' => 'can:edit contactreports',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.contactreports.delete',
		'uses' => 'ReportsController@delete',
		'middleware' => 'can:delete contactreports',
	])->where('id', '[0-9]+');

	// Comments
	$router->group(['prefix' => 'comments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.contactreports.comments',
			'uses' => 'CommentsController@index',
		]);
		$router->post('/', [
			'as' => 'api.contactreports.comments.create',
			'uses' => 'CommentsController@create',
			'middleware' => 'can:create contactreports',
		]);
		$router->get('{comment}', [
			'as' => 'api.contactreports.comments.read',
			'uses' => 'CommentsController@read',
		])->where('comment', '[0-9]+');
		$router->put('{comment}', [
			'as' => 'api.contactreports.comments.update',
			'uses' => 'CommentsController@update',
			'middleware' => 'can:edit contactreports',
		])->where('comment', '[0-9]+');
		$router->delete('{comment}', [
			'as' => 'api.contactreports.comments.delete',
			'uses' => 'CommentsController@delete',
			'middleware' => 'can:delete contactreports',
		])->where('comment', '[0-9]+');
	});
});
