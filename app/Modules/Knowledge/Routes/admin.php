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
	$router->get('/select', [
		'as' => 'admin.knowledge.select',
		'uses' => 'PagesController@select',
		'middleware' => 'can:create knowledge',
	]);
	$router->post('/attach', [
		'as' => 'admin.knowledge.attach',
		'uses' => 'PagesController@attach',
		'middleware' => 'can:create knowledge|edit knowledge',
	]);
	$router->post('/store', [
		'as' => 'admin.knowledge.store',
		'uses' => 'PagesController@store',
		'middleware' => 'can:create knowledge|edit knowledge',
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
	$router->match(['get', 'post'], '/orderup/{id}', [
		'as'   => 'admin.knowledge.orderup',
		'uses' => 'PagesController@reorder',
		'middleware' => 'can:edit.state knowledge',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/orderdown/{id?}', [
		'as'   => 'admin.knowledge.orderdown',
		'uses' => 'PagesController@reorder',
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
	$router->group(['prefix' => 'snippets'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.knowledge.snippets',
			'uses' => 'SnippetsController@index',
			'middleware' => 'can:manage knowledge',
		]);
		$router->get('/create', [
			'as' => 'admin.knowledge.snippets.create',
			'uses' => 'SnippetsController@create',
			'middleware' => 'can:create knowledge',
		]);
		$router->post('/store', [
			'as' => 'admin.knowledge.snippets.store',
			'uses' => 'SnippetsController@store',
			'middleware' => 'can:create knowledge|edit knowledge',
		]);
		$router->post('/attach', [
			'as' => 'admin.knowledge.snippets.attach',
			'uses' => 'SnippetsController@attach',
			'middleware' => 'can:create knowledge|edit knowledge',
		]);
		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'admin.knowledge.snippets.orderup',
			'uses' => 'SnippetsController@reorder',
			'middleware' => 'can:edit.state knowledge',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'admin.knowledge.snippets.orderdown',
			'uses' => 'SnippetsController@reorder',
			'middleware' => 'can:edit.state knowledge',
		])->where('id', '[0-9]+');
		$router->get('/edit/{id}', [
			'as' => 'admin.knowledge.snippets.edit',
			'uses' => 'SnippetsController@edit',
			'middleware' => 'can:edit knowledge',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.knowledge.snippets.delete',
			'uses' => 'SnippetsController@delete',
			'middleware' => 'can:delete knowledge',
		]);
		$router->post('/cancel', [
			'as' => 'admin.knowledge.snippets.cancel',
			'uses' => 'SnippetsController@cancel',
		]);
	});
});
