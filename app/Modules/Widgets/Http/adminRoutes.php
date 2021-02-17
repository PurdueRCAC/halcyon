<?php

use Illuminate\Routing\Router;

$router->group(['prefix' => 'widgets'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.widgets.index',
		'uses' => 'WidgetsController@index',
		'middleware' => 'can:manage widgets',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.widgets.cancel',
		'uses' => 'WidgetsController@cancel',
	]);
	$router->get('/positions/{client}', [
		'as'   => 'admin.widgets.positions',
		'uses' => 'WidgetsController@positions',
		'middleware' => 'can:edit widgets',
	]);
	$router->get('/select', [
		'as'   => 'admin.widgets.select',
		'uses' => 'WidgetsController@select',
		'middleware' => 'can:create widgets',
	]);
	$router->get('/create', [
		'as'   => 'admin.widgets.create',
		'uses' => 'WidgetsController@create',
		'middleware' => 'can:create widgets',
	]);
	$router->post('/store', [
		'as'   => 'admin.widgets.store',
		'uses' => 'WidgetsController@store',
		'middleware' => 'can:create widgets,edit widgets',
	]);
	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'admin.widgets.publish',
		'uses' => 'WidgetsController@state',
		'middleware' => 'can:edit.state widgets',
	]);
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'admin.widgets.unpublish',
		'uses' => 'WidgetsController@state',
		'middleware' => 'can:edit.state widgets',
	]);
	$router->get('/trash/{id}', [
		'as'   => 'admin.widgets.trash',
		'uses' => 'WidgetsController@trash',
		'middleware' => 'can:edit.state widgets',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.widgets.edit',
		'uses' => 'WidgetsController@edit',
		'middleware' => 'can:edit widgets',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.widgets.delete',
		'uses' => 'WidgetsController@delete',
		'middleware' => 'can:delete widgets',
	]);

	$router->match(['get', 'post'], '/orderup/{id}', [
		'as'   => 'admin.widgets.orderup',
		'uses' => 'WidgetsController@reorder',
		'middleware' => 'can:edit.state widgets',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/orderdown/{id?}', [
		'as'   => 'admin.widgets.orderdown',
		'uses' => 'WidgetsController@reorder',
		'middleware' => 'can:edit.state widgets',
	])->where('id', '[0-9]+');
	/*$router->post('/reorder', [
		'as'   => 'admin.widgets.reorder',
		'uses' => 'WidgetsController@reorder',
	]);*/
	$router->post('/saveorder', [
		'as'   => 'admin.widgets.saveorder',
		'uses' => 'WidgetsController@saveorder',
	]);

	$router->post('/checkin', [
		'as'   => 'admin.widgets.checkin',
		'uses' => 'WidgetsController@checkin',
	]);
	/*$router->post('/batch', [
		'as'   => 'admin.widgets.batch',
		'uses' => 'WidgetsController@batch',
		'middleware' => 'can:edit.state widgets',
	]);*/
});
