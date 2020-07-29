<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'tags'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.tags.index',
		'uses' => 'TagsController@index',
		//'middleware' => 'can:tag.tags.index',
	]);
	$router->post('/', [
		'as' => 'api.tags.create',
		'uses' => 'TagsController@create',
		//'middleware' => 'can:tag.tags.create',
	]);
	$router->get('{id}', [
		'as' => 'api.tags.read',
		'uses' => 'TagsController@read',
		//'middleware' => 'can:tag.tags.edit',
	]);
	$router->put('{id}', [
		'as' => 'api.tags.update',
		'uses' => 'TagsController@update',
		//'middleware' => 'can:tag.tags.edit',
	]);
	$router->delete('{id}', [
		'as' => 'api.tags.delete',
		'uses' => 'TagsController@delete',
		//'middleware' => 'can:tag.tags.destroy',
	]);
});
