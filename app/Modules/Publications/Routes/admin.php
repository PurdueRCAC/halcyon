<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'publications'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.publications.index',
		'uses' => 'PublicationsController@index',
		'middleware' => 'can:manage publications',
	]);
	$router->get('/create', [
		'as'   => 'admin.publications.create',
		'uses' => 'PublicationsController@create',
		'middleware' => 'can:create publications',
	]);
	$router->post('/store', [
		'as'   => 'admin.publications.store',
		'uses' => 'PublicationsController@store',
		'middleware' => 'can:create publications|edit publications',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.publications.edit',
		'uses' => 'PublicationsController@edit',
		'middleware' => 'can:edit publications',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.publications.delete',
		'uses' => 'PublicationsController@delete',
		'middleware' => 'can:delete publications',
	]);
	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'admin.publications.publish',
		'uses' => 'PublicationsController@state',
		'middleware' => 'can:edit.state publications',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'admin.publications.unpublish',
		'uses' => 'PublicationsController@state',
		'middleware' => 'can:edit.state publications',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'admin.publications.restore',
		'uses' => 'PublicationsController@restore',
		'middleware' => 'can:edit.state publications',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.publications.types.cancel',
		'uses' => 'TypesController@cancel',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.publications.cancel',
		'uses' => 'PublicationsController@cancel',
	]);

	$router->group(['prefix' => 'types'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.publications.types',
			'uses' => 'TypesController@index',
		]);
		$router->get('/create', [
			'as'   => 'admin.publications.types.create',
			'uses' => 'TypesController@create',
			'middleware' => 'can:create publications',
		]);
		$router->post('/store', [
			'as'   => 'admin.publications.types.store',
			'uses' => 'TypesController@store',
			'middleware' => 'can:create publications|edit publications',
		]);
		$router->get('/{id}', [
			'as'   => 'admin.publications.types.edit',
			'uses' => 'TypesController@edit',
			'middleware' => 'can:edit publications',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.publications.types.delete',
			'uses' => 'TypesController@delete',
			'middleware' => 'can:delete publications',
		]);
	});

	$router->group(['prefix' => 'authors'], function (Router $router)
	{
		$router->match(['get', 'post'], '/restore/{id?}', [
			'as'   => 'admin.publications.authors.restore',
			'uses' => 'AuthorsController@restore',
			'middleware' => 'can:edit.state publications',
		])->where('id', '[0-9]+');

		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'admin.publications.authors.orderup',
			'uses' => 'AuthorsController@reorder',
			'middleware' => 'can:edit.state publications',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'admin.publications.authors.orderdown',
			'uses' => 'AuthorsController@reorder',
			'middleware' => 'can:edit.state publications',
		])->where('id', '[0-9]+');

		$router->get('/create', [
			'as'   => 'admin.publications.authors.create',
			'uses' => 'AuthorsController@create',
			'middleware' => 'can:create publications',
		]);
		$router->post('/store', [
			'as'   => 'admin.publications.authors.store',
			'uses' => 'AuthorsController@store',
			'middleware' => 'can:create publications|edit publications',
		]);
		$router->get('/{id}', [
			'as'   => 'admin.publications.authors.edit',
			'uses' => 'AuthorsController@edit',
			'middleware' => 'can:edit publications',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.publications.authors.delete',
			'uses' => 'AuthorsController@delete',
			'middleware' => 'can:delete publications',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'admin.publications.authors.cancel',
			'uses' => 'AuthorsController@cancel',
		]);
	});
});
