<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'issues', 'middleware' => ['auth:api', 'can:manage issues']], function (Router $router)
{
	// Comments
	$router->group(['prefix' => 'comments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.issues.comments',
			'uses' => 'CommentsController@index',
		]);
		$router->post('/', [
			'as' => 'api.issues.comments.create',
			'uses' => 'CommentsController@create',
			'middleware' => 'can:create issues',
		]);
		$router->get('{comment}', [
			'as' => 'api.issues.comments.read',
			'uses' => 'CommentsController@read',
		])->where('comment', '[0-9]+');
		$router->put('{comment}', [
			'as' => 'api.issues.comments.update',
			'uses' => 'CommentsController@update',
			'middleware' => 'can:edit issues',
		])->where('comment', '[0-9]+');
		$router->delete('{comment}', [
			'as' => 'api.issues.comments.delete',
			'uses' => 'CommentsController@delete',
			'middleware' => 'can:delete issues',
		])->where('comment', '[0-9]+');
	});

	$router->get('/', [
		'as'   => 'api.issues.index',
		'uses' => 'IssuesController@index',
	]);
	$router->post('/', [
		'as' => 'api.issues.create',
		'uses' => 'IssuesController@create',
		'middleware' => 'can:create issues',
	]);
	$router->get('{id}', [
		'as'   => 'api.issues.read',
		'uses' => 'IssuesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.issues.update',
		'uses' => 'IssuesController@update',
		'middleware' => 'can:edit issues',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.issues.delete',
		'uses' => 'IssuesController@delete',
		'middleware' => 'can:delete issues',
	])->where('id', '[0-9]+');
});
