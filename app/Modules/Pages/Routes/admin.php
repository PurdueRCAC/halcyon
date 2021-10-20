<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'pages'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.pages.index',
		'uses' => 'PagesController@index',
		'middleware' => 'can:manage pages',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.pages.cancel',
		'uses' => 'PagesController@cancel',
	]);
	$router->get('/create', [
		'as'   => 'admin.pages.create',
		'uses' => 'PagesController@create',
		'middleware' => 'can:create pages',
	]);
	$router->post('/store', [
		'as'   => 'admin.pages.store',
		'uses' => 'PagesController@store',
		'middleware' => 'can:create pages|edit pages',
	]);
	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'admin.pages.publish',
		'uses' => 'PagesController@state',
		'middleware' => 'can:edit.state pages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'admin.pages.unpublish',
		'uses' => 'PagesController@state',
		'middleware' => 'can:edit.state pages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.pages.restore',
		'uses' => 'PagesController@restore',
		'middleware' => 'can:edit.state pages',
	])->where('id', '[0-9]+');
	$router->get('/{id}', [
		'as'   => 'admin.pages.edit',
		'uses' => 'PagesController@edit',
		'middleware' => 'can:edit pages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.pages.delete',
		'uses' => 'PagesController@delete',
		'middleware' => 'can:delete pages',
	]);
	$router->match(['get', 'post'], '/history/{id}', [
		'as'   => 'admin.pages.history',
		'uses' => 'PagesController@history',
		'middleware' => 'can:edit pages',
	])->where('id', '[0-9]+');
});
