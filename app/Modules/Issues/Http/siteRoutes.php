<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'issues', 'middleware' => ['auth.admin', 'can:manage issues']], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.issues.index',
		'uses' => 'IssuesController@index',
	]);

	// To Dos
	$router->group(['prefix' => 'todos'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'site.issues.todos',
			'uses' => 'ToDosController@index',
			//'middleware' => 'can:manage issues',
		]);
		$router->get('/create', [
			'as' => 'site.issues.todos.create',
			'uses' => 'ToDosController@create',
			'middleware' => 'can:create issues',
		]);
		$router->post('/store', [
			'as' => 'site.issues.todos.store',
			'uses' => 'ToDosController@store',
			'middleware' => 'can:create issues,edit issues',
		]);
		$router->get('/edit/{id}', [
			'as' => 'site.issues.todos.edit',
			'uses' => 'ToDosController@edit',
			'middleware' => 'can:edit issues',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'site.issues.todos.delete',
			'uses' => 'ToDosController@delete',
			'middleware' => 'can:delete issues',
		]);
		$router->match(['get', 'post'], '/cancel', [
			'as' => 'site.issues.todos.cancel',
			'uses' => 'ToDosController@cancel',
		]);
	});

	$router->get('create', [
		'as' => 'site.issues.create',
		'uses' => 'IssuesController@create',
		'middleware' => 'can:create issues',
	]);
	$router->post('store', [
		'as' => 'site.issues.store',
		'uses' => 'IssuesController@store',
	]);

	$router->get('{id}', [
		'as' => 'site.issues.show',
		'uses' => 'IssuesController@show',
		'middleware' => 'can:edit issues',
	])->where('id', '[0-9]+');

	$router->get('{id}/edit', [
		'as' => 'site.issues.edit',
		'uses' => 'IssuesController@edit',
		'middleware' => 'can:edit issues',
	])->where('id', '[0-9]+');

	$router->put('{id}', [
		'as' => 'site.issues.update',
		'uses' => 'IssuesController@update',
		'middleware' => 'can:edit issues,can:create issues',
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'site.issues.delete',
		'uses' => 'IssuesController@delete',
		'middleware' => 'can:delete issues',
	])->where('id', '[0-9]+');
});
