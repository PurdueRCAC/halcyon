<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'publications'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'site.publications.index',
		'uses' => 'PublicationsController@index',
	]);
	$router->get('/create', [
		'as'   => 'site.publications.create',
		'uses' => 'PublicationsController@create',
		'middleware' => ['auth', 'can:create publications'],
	]);
	$router->get('/import', [
		'as'   => 'site.publications.import',
		'uses' => 'PublicationsController@import',
		'middleware' => ['auth', 'can:create publications'],
	]);
	$router->post('/store', [
		'as'   => 'site.publications.store',
		'uses' => 'PublicationsController@store',
		'middleware' => ['auth', 'can:create publications|edit publications'],
	]);
	$router->get('/download/{id}', [
		'as'   => 'site.publications.download',
		'uses' => 'PublicationsController@download',
	])->where('id', '[0-9]+');
	$router->get('/edit/{id}', [
		'as'   => 'site.publications.edit',
		'uses' => 'PublicationsController@edit',
		'middleware' => ['auth', 'can:edit publications'],
	])->where('id', '[0-9]+');
	$router->get('/delete/{id}', [
		'as'   => 'site.publications.delete',
		'uses' => 'PublicationsController@delete',
		'middleware' => ['auth', 'can:delete publications'],
	]);

	$router->group(['prefix' => 'authors'], function (Router $router)
	{
		$router->match(['get', 'post'], '/restore/{id?}', [
			'as'   => 'site.publications.authors.restore',
			'uses' => 'AuthorsController@restore',
			'middleware' => 'can:edit.state publications',
		])->where('id', '[0-9]+');

		$router->match(['get', 'post'], '/orderup/{id}', [
			'as'   => 'site.publications.authors.orderup',
			'uses' => 'AuthorsController@reorder',
			'middleware' => 'can:edit.state publications',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/orderdown/{id?}', [
			'as'   => 'site.publications.authors.orderdown',
			'uses' => 'AuthorsController@reorder',
			'middleware' => 'can:edit.state publications',
		])->where('id', '[0-9]+');

		$router->get('/create', [
			'as'   => 'site.publications.authors.create',
			'uses' => 'AuthorsController@create',
			'middleware' => 'can:create publications',
		]);
		$router->post('/store', [
			'as'   => 'site.publications.authors.store',
			'uses' => 'AuthorsController@store',
			'middleware' => 'can:create publications|edit publications',
		]);
		$router->get('/{id}', [
			'as'   => 'site.publications.authors.edit',
			'uses' => 'AuthorsController@edit',
			'middleware' => 'can:edit publications',
		])->where('id', '[0-9]+');
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'site.publications.authors.delete',
			'uses' => 'AuthorsController@delete',
			'middleware' => 'can:delete publications',
		]);
		$router->match(['get', 'post'], 'cancel', [
			'as' => 'site.publications.authors.cancel',
			'uses' => 'AuthorsController@cancel',
		]);
	});
});
