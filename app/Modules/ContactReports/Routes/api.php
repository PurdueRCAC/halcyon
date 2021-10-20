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
		$router->get('{id}', [
			'as' => 'api.contactreports.comments.read',
			'uses' => 'CommentsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.contactreports.comments.update',
			'uses' => 'CommentsController@update',
			'middleware' => 'can:edit contactreports',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.contactreports.comments.delete',
			'uses' => 'CommentsController@delete',
			'middleware' => 'can:delete contactreports',
		])->where('id', '[0-9]+');
	});

	// Types
	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.contactreports.types',
			'uses' => 'TypesController@index',
		]);
		$router->post('/', [
			'as' => 'api.contactreports.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create contactreports',
		]);
		$router->get('{id}', [
			'as' => 'api.contactreports.types.read',
			'uses' => 'TypesController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.contactreports.types.update',
			'uses' => 'TypesController@update',
			'middleware' => 'can:edit contactreports',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.contactreports.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete contactreports',
		])->where('id', '[0-9]+');
	});

	// Follow users
	$router->group(['prefix' => 'followusers'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.contactreports.followusers',
			'uses' => 'FollowUsersController@index',
		]);
		$router->post('/', [
			'as' => 'api.contactreports.followusers.create',
			'uses' => 'FollowUsersController@create',
			'middleware' => 'can:create contactreports',
		]);
		$router->get('{id}', [
			'as' => 'api.contactreports.followusers.read',
			'uses' => 'FollowUsersController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.contactreports.followusers.update',
			'uses' => 'FollowUsersController@update',
			'middleware' => 'can:edit contactreports',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.contactreports.followusers.delete',
			'uses' => 'FollowUsersController@delete',
			'middleware' => 'can:delete contactreports',
		])->where('id', '[0-9]+');
	});

	// Follow users
	$router->group(['prefix' => 'followgroups'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.contactreports.followgroups',
			'uses' => 'FollowGroupsController@index',
		]);
		$router->post('/', [
			'as' => 'api.contactreports.followgroups.create',
			'uses' => 'FollowGroupsController@create',
			'middleware' => 'can:create contactreports',
		]);
		$router->get('{id}', [
			'as' => 'api.contactreports.followgroups.read',
			'uses' => 'FollowGroupsController@read',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.contactreports.followgroups.update',
			'uses' => 'FollowGroupsController@update',
			'middleware' => 'can:edit contactreports',
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.contactreports.followgroups.delete',
			'uses' => 'FollowGroupsController@delete',
			'middleware' => 'can:delete contactreports',
		])->where('id', '[0-9]+');
	});
});
