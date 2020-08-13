<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'knowledge'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.knowledge.index',
		'uses' => 'PagesController@index',
		'middleware' => 'can:manage knowledge',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.knowledge.cancel',
		'uses' => 'PagesController@cancel',
	]);
	$router->get('/create', [
		'as' => 'admin.knowledge.create',
		'uses' => 'PagesController@create',
		'middleware' => 'can:create knowledge',
	]);
	$router->post('/store', [
		'as' => 'admin.knowledge.store',
		'uses' => 'PagesController@store',
		'middleware' => 'can:create knowledge,edit knowledge',
	]);
	$router->get('/rebuild', [
		'as' => 'admin.knowledge.rebuild',
		'uses' => 'PagesController@rebuild',
		'middleware' => 'can:edit knowledge',
	])->where('id', '[0-9]+');
	$router->get('/{id}', [
		'as' => 'admin.knowledge.edit',
		'uses' => 'PagesController@edit',
		'middleware' => 'can:edit knowledge',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'admin.knowledge.publish',
		'uses' => 'PagesController@state',
		'middleware' => 'can:edit.state knowledge',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'admin.knowledge.unpublish',
		'uses' => 'PagesController@state',
		'middleware' => 'can:edit.state knowledge',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.knowledge.restore',
		'uses' => 'PagesController@restore',
		'middleware' => 'can:edit.state knowledge',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.knowledge.delete',
		'uses' => 'PagesController@delete',
		'middleware' => 'can:delete knowledge',
	]);

	// Products
	$router->group(['prefix' => 'blocks'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.knowledge.blocks',
			'uses' => 'BlocksController@index',
			'middleware' => 'can:manage knowledge',
		]);
		$router->get('/create', [
			'as' => 'admin.knowledge.blocks.create',
			'uses' => 'BlocksController@create',
			'middleware' => 'can:create knowledge',
		]);
		$router->post('/store', [
			'as' => 'admin.knowledge.blocks.store',
			'uses' => 'BlocksController@store',
			'middleware' => 'can:create knowledge,edit knowledge',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.knowledge.blocks.edit',
			'uses' => 'BlocksController@edit',
			'middleware' => 'can:edit knowledge',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.knowledge.blocks.delete',
			'uses' => 'BlocksController@delete',
			'middleware' => 'can:delete knowledge',
		]);
		$router->post('/cancel', [
			'as' => 'admin.knowledge.blocks.cancel',
			'uses' => 'BlocksController@cancel',
		]);
	});
});
