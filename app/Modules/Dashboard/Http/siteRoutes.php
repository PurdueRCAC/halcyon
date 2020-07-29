<?php

use Illuminate\Routing\Router;

/** @var Router $router */
/*$router->group(['prefix' => 'widgets'], function (Router $router) {
	$router->get('index', [
		'as' => 'admin.widgets.index',
		'uses' => 'ListenersController@index',
		'middleware' => 'can:widgets.widgets.index',
	]);
	$router->get('widgets/create', [
		'as' => 'admin.widget.widget.create',
		'uses' => 'widgetController@create',
		'middleware' => 'can:widget.widgets.create',
	]);
	$router->post('widgets', [
		'as' => 'admin.widget.widget.store',
		'uses' => 'widgetController@store',
		'middleware' => 'can:widget.widgets.create',
	]);å
	$router->get('widgets/{widget__widget}/edit', [
		'as' => 'admin.widget.widget.edit',
		'uses' => 'widgetController@edit',
		'middleware' => 'can:widget.widgets.edit',
	]);
	$router->put('widgets/{widget__widget}', [
		'as' => 'admin.widget.widget.update',
		'uses' => 'widgetController@update',
		'middleware' => 'can:widget.widgets.edit',
	]);
	$router->delete('widgets/{widget__widget}', [
		'as' => 'admin.widget.widget.destroy',
		'uses' => 'widgetController@destroy',
		'middleware' => 'can:widget.widgets.destroy',
	]);
});*/
$router->get('widgets', 'ListenersController@index');