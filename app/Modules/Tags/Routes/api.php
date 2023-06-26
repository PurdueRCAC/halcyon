<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'tags', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.tags.index',
		'uses' => 'TagsController@index',
	]);
	$router->post('/', [
		'as' => 'api.tags.create',
		'uses' => 'TagsController@create',
		'middleware' => ['can:create tags'],
	]);
	$router->get('{id}', [
		'as' => 'api.tags.read',
		'uses' => 'TagsController@read',
	]);
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.tags.update',
		'uses' => 'TagsController@update',
		'middleware' => ['can:edit tags'],
	]);
	$router->delete('{id}', [
		'as' => 'api.tags.delete',
		'uses' => 'TagsController@delete',
		'middleware' => ['can:delete tags'],
	]);
});
