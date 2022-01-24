<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'issues', 'middleware' => ['auth', 'can:manage issues']], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.issues.index',
		'uses' => 'IssuesController@index',
	]);

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
		'middleware' => 'can:edit issues|can:create issues',
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'site.issues.delete',
		'uses' => 'IssuesController@delete',
		'middleware' => 'can:delete issues',
	])->where('id', '[0-9]+');
});
