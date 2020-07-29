<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'media'], function (Router $router)
{
	// Media
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.media.index',
		'uses' => 'MediaController@index',
		'middleware' => 'can:manage media',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.media.cancel',
		'uses' => 'MediaController@cancel',
	]);
	$router->get('/upload', [
		'as' => 'admin.media.upload',
		'uses' => 'MediaController@upload',
		'middleware' => 'can:create media',
	]);
	$router->get('/download', [
		'as'   => 'admin.media.download',
		'uses' => 'MediaController@download',
	]);
	$router->get('/new', [
		'as' => 'admin.media.newdir',
		'uses' => 'MediaController@newdir',
		'middleware' => 'can:create media',
	]);
	$router->post('/store', [
		'as' => 'admin.media.store',
		'uses' => 'MediaController@store',
		'middleware' => 'can:create media,edit media',
	]);
	$router->get('/edit/{id}', [
		'as' => 'admin.media.edit',
		'uses' => 'MediaController@edit',
		'middleware' => 'can:edit media',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{file?}', [
		'as'   => 'admin.media.delete',
		'uses' => 'MediaController@delete',
		'middleware' => 'can:delete media',
	]);
	$router->get('/info/{file}', [
		'as'   => 'admin.media.info',
		'uses' => 'MedialistController@info',
	]);
	$router->get('/path/{file}', [
		'as'   => 'admin.media.path',
		'uses' => 'MedialistController@path',
	]);
	$router->get('/files', [
		'as'   => 'admin.media.medialist',
		'uses' => 'MedialistController@index',
	]);

	$router->group(['prefix' => 'folder'], function (Router $router)
	{
		// Media
		$router->post('/', [
			'as'   => 'admin.media.folder.create',
			'uses' => 'FolderController@create',
			'middleware' => 'can:manage media',
		]);
		$router->get('/delete', [
			'as'   => 'admin.media.folder.delete',
			'uses' => 'FolderController@delete',
		]);
	});
});
