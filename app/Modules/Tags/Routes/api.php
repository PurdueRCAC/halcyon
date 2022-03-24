<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'tags'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.tags.index',
		'uses' => 'TagsController@index',
		//'middleware' => ['auth:api', 'can:tag.tags.index'],
	]);
	$router->post('/', [
		'as' => 'api.tags.create',
		'uses' => 'TagsController@create',
		'middleware' => ['auth:api', 'can:create tags'],
	]);
	$router->get('{id}', [
		'as' => 'api.tags.read',
		'uses' => 'TagsController@read',
		//'middleware' => ['auth:api', 'can:tag.tags.edit'],
	]);
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.tags.update',
		'uses' => 'TagsController@update',
		'middleware' => ['auth:api', 'can:edit tags'],
	]);
	$router->delete('{id}', [
		'as' => 'api.tags.delete',
		'uses' => 'TagsController@delete',
		'middleware' => ['auth:api', 'can:delete tags'],
	]);
});
