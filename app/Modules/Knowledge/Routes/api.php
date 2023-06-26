<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'knowledge', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.knowledge.index',
		'uses' => 'PagesController@index',
	]);
	$router->post('/', [
		'as' => 'api.knowledge.create',
		'uses' => 'PagesController@create',
		'middleware' => ['can:create knowledge'],
	]);
	$router->get('{id}', [
		'as'   => 'api.knowledge.read',
		'uses' => 'PagesController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.knowledge.update',
		'uses' => 'PagesController@update',
		'middleware' => ['can:edit knowledge'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.knowledge.delete',
		'uses' => 'PagesController@delete',
		'middleware' => ['can:delete knowledge'],
	])->where('id', '[0-9]+');
	$router->get('diff', [
		'as'   => 'api.knowledge.diff',
		'uses' => 'PagesController@diff',
	]);

	// Page snippets
	$router->group(['prefix' => '/snippets', 'middleware' => ['can:manage knowledge']], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.knowledge.snippets',
			'uses' => 'SnippetsController@index',
		]);
		$router->post('/', [
			'as' => 'api.knowledge.snippets.create',
			'uses' => 'SnippetsController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.knowledge.snippets.read',
			'uses' => 'SnippetsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.knowledge.snippets.update',
			'uses' => 'SnippetsController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id', [
			'as' => 'api.knowledge.snippets.delete',
			'uses' => 'SnippetsController@delete',
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => '/feedback'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.knowledge.feedback',
			'uses' => 'FeedbackController@index',
		]);
		$router->post('/', [
			'as' => 'api.knowledge.feedback.create',
			'uses' => 'FeedbackController@create',
		]);
		$router->get('{id}', [
			'as' => 'api.knowledge.feedback.read',
			'uses' => 'FeedbackController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.knowledge.feedback.update',
			'uses' => 'FeedbackController@update',
		])->where('id', '[0-9]+');
		$router->delete('{id', [
			'as' => 'api.knowledge.feedback.delete',
			'uses' => 'FeedbackController@delete',
			'middleware' => ['can:delete knowledge'],
		])->where('id', '[0-9]+');
	});
});
