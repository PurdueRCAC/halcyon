<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'media', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->group(['prefix' => 'folder', 'middleware' => 'can:manage media'], function (Router $router)
	{
		// Media
		$router->post('/', [
			'as'   => 'api.media.folder.create',
			'uses' => 'FolderController@create',
		]);
		$router->put('/', [
			'as'   => 'api.media.folder.update',
			'uses' => 'FolderController@update',
		]);
		$router->delete('/', [
			'as'   => 'api.media.folder.delete',
			'uses' => 'FolderController@delete',
		]);
	});

	$router->get('/', [
		'as'   => 'api.media.index',
		'uses' => 'MediaController@index',
	]);
	$router->post('/', [
		'as' => 'api.media.upload',
		'uses' => 'MediaController@upload',
		'middleware' => 'can:create media',
	]);
	$router->get('/tree', [
		'as' => 'api.media.tree',
		'uses' => 'MediaController@tree',
	]);
	$router->get('/content', [
		'as' => 'api.media.content',
		'uses' => 'MediaController@content',
	]);
	$router->post('/layout', [
		'as'   => 'api.media.layout',
		'uses' => 'MediaController@layout',
	]);
	$router->get('/download', [
		'as' => 'api.media.download',
		'uses' => 'MediaController@download',
	]);
	$router->put('/rename', [
		'as'   => 'api.media.rename',
		'uses' => 'MediaController@update',
		'middleware' => 'can:edit media',
	]);
	$router->put('/move', [
		'as'   => 'api.media.move',
		'uses' => 'MediaController@update',
		'middleware' => 'can:edit media',
	]);
	/*$router->get('{path}', [
		'as'   => 'api.media.read',
		'uses' => 'MediaController@read',
	]);
	$router->put('{path}', [
		'as'   => 'api.media.upload',
		'uses' => 'MediaController@update',
	]);*/
	$router->delete('/', [
		'as' => 'api.media.delete',
		'uses' => 'MediaController@delete',
		'middleware' => 'can:delete media',
	]);
});
