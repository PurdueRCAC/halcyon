<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'pages'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.pages.index',
		'uses' => 'PagesController@index',
	]);
	$router->post('/', [
		'as' => 'api.pages.create',
		'uses' => 'PagesController@create',
		'middleware' => ['auth:api', 'can:create pages']
	]);
	$router->get('{id}', [
		'as'   => 'api.pages.read',
		'uses' => 'PagesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'api.pages.update',
		'uses' => 'PagesController@update',
		'middleware' => ['auth:api', 'can:edit pages']
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.pages.delete',
		'uses' => 'PagesController@delete',
		'middleware' => ['auth:api', 'can:delete pages']
	])->where('id', '[0-9]+');
});
