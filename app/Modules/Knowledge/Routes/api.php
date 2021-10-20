<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'knowledge'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.knowledge.index',
		'uses' => 'PagesController@index',
	]);
	$router->post('/', [
		'as' => 'api.knowledge.create',
		'uses' => 'PagesController@create',
		'middleware' => ['auth:api', 'can:create knowledge'],
	]);
	$router->get('{id}', [
		'as'   => 'api.knowledge.read',
		'uses' => 'PagesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.knowledge.update',
		'uses' => 'PagesController@update',
		'middleware' => ['auth:api', 'can:edit knowledge'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.knowledge.delete',
		'uses' => 'PagesController@delete',
		'middleware' => ['auth:api', 'can:delete knowledge'],
	])->where('id', '[0-9]+');

	// Page snippets
	$router->group(['prefix' => '/snippets', 'middleware' => ['auth:api', 'can:manage knowledge']], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.knowledge.snippets',
			'uses' => 'SnippetsController@index',
			//'middleware' => ['auth:api', 'can:manage knowledge'],
		]);
		$router->post('/', [
			'as' => 'api.knowledge.snippets.create',
			'uses' => 'SnippetsController@create',
			//'middleware' => ['auth:api', 'can:create knowledge'],
		]);
		$router->get('{id}', [
			'as' => 'api.knowledge.snippets.read',
			'uses' => 'SnippetsController@read',
			//'middleware' => ['auth:api', 'can:manage knowledge'],
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.knowledge.snippets.update',
			'uses' => 'SnippetsController@update',
			//'middleware' => ['auth:api', 'can:edit knowledge'],
		])->where('id', '[0-9]+');
		$router->delete('{id', [
			'as' => 'api.knowledge.snippets.delete',
			'uses' => 'SnippetsController@delete',
			//'middleware' => ['auth:api', 'can:delete knowledge'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => '/feedback'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.knowledge.feedback',
			'uses' => 'FeedbackController@index',
			'middleware' => ['auth:api'],
		]);
		$router->post('/', [
			'as' => 'api.knowledge.feedback.create',
			'uses' => 'FeedbackController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.knowledge.feedback.read',
			'uses' => 'FeedbackController@read',
			'middleware' => ['auth:api'],
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.knowledge.feedback.update',
			'uses' => 'FeedbackController@update',
			'middleware' => ['auth:api'],
		])->where('id', '[0-9]+');
		$router->delete('{id', [
			'as' => 'api.knowledge.feedback.delete',
			'uses' => 'FeedbackController@delete',
			'middleware' => ['auth:api', 'can:delete knowledge'],
		])->where('id', '[0-9]+');
	});
});
