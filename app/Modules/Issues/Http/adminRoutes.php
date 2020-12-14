<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'issues', 'middleware' => 'can:manage issues'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.issues.index',
		'uses' => 'IssuesController@index',
		//'middleware' => 'can:manage issues',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.issues.cancel',
		'uses' => 'IssuesController@cancel',
	]);
	$router->get('/create', [
		'as' => 'admin.issues.create',
		'uses' => 'IssuesController@create',
		'middleware' => 'can:create issues',
	]);
	$router->post('/store', [
		'as' => 'admin.issues.store',
		'uses' => 'IssuesController@store',
		'middleware' => 'can:create issues,edit issues',
	]);
	$router->get('/edit/{id}', [
		'as' => 'admin.issues.edit',
		'uses' => 'IssuesController@edit',
		'middleware' => 'can:edit issues',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.issues.delete',
		'uses' => 'IssuesController@delete',
		'middleware' => 'can:delete issues',
	]);

	// Comments
	$router->group(['prefix' => '{issue}/comments'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.issues.comments',
			'uses' => 'CommentsController@index',
			//'middleware' => 'can:manage issues',
		]);
		$router->get('/create', [
			'as' => 'admin.issues.comments.create',
			'uses' => 'CommentsController@create',
			'middleware' => 'can:create issues',
		]);
		$router->post('/store', [
			'as' => 'admin.issues.comments.store',
			'uses' => 'CommentsController@store',
			'middleware' => 'can:create issues,edit issues',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.issues.comments.edit',
			'uses' => 'CommentsController@edit',
			'middleware' => 'can:edit issues',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.issues.comments.delete',
			'uses' => 'CommentsController@delete',
			'middleware' => 'can:delete issues',
		]);
		$router->match(['get', 'post'], '/cancel', [
			'as' => 'admin.issues.comments.cancel',
			'uses' => 'CommentsController@cancel',
		]);
	});

	// Comments
	$router->group(['prefix' => 'todos'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.issues.todos',
			'uses' => 'ToDosController@index',
			//'middleware' => 'can:manage issues',
		]);
		$router->get('/create', [
			'as' => 'admin.issues.todos.create',
			'uses' => 'ToDosController@create',
			'middleware' => 'can:create issues',
		]);
		$router->post('/store', [
			'as' => 'admin.issues.todos.store',
			'uses' => 'ToDosController@store',
			'middleware' => 'can:create issues,edit issues',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.issues.todos.edit',
			'uses' => 'ToDosController@edit',
			'middleware' => 'can:edit issues',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.issues.todos.delete',
			'uses' => 'ToDosController@delete',
			'middleware' => 'can:delete issues',
		]);
		$router->match(['get', 'post'], '/cancel', [
			'as' => 'admin.issues.todos.cancel',
			'uses' => 'ToDosController@cancel',
		]);
	});
});
