<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'media'], function (Router $router) {
	$router->get('/', [
		'as' => 'site.media.index',
		'uses' => 'MediaController@index',
		//'middleware' => 'can:tag.tags.index',
	]);
	$router->get('rss/{name?}', [
		'as' => 'site.media.rss',
		'uses' => 'MediaController@rss',
		//'middleware' => 'can:tag.tags.index',
	])->where('name', '[a-zA-Z\-_]+');
	$router->get('search', [
		'as' => 'site.media.search',
		'uses' => 'MediaController@search',
		//'middleware' => 'can:tag.tags.index',
	]);

	$router->get('manage', [
		'as' => 'site.media.manage',
		'uses' => 'MediaController@manage',
		'middleware' => 'can:manage media',
	]);
	$router->post('store', [
		'as' => 'site.media.store',
		'uses' => 'MediaController@store',
		//'middleware' => 'can:tag.tags.create',
	]);

	$router->get('{id}', [
		'as' => 'site.media.show',
		'uses' => 'MediaController@show',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');
	$router->get('{id}/edit', [
		'as' => 'site.media.edit',
		'uses' => 'MediaController@edit',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'site.media.update',
		'uses' => 'MediaController@update',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'site.media.destroy',
		'uses' => 'MediaController@destroy',
		//'middleware' => 'can:tag.tags.destroy',
	])->where('id', '[0-9]+');

	$router->get('{name}', [
		'as' => 'site.media.type',
		'uses' => 'MediaController@type',
		//'middleware' => 'can:tag.tags.index',
	])->where('name', '[a-zA-Z\-_]+');
});
//$router->get('media', 'mediaController@index');