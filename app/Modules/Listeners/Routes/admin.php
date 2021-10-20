<?php

use Illuminate\Routing\Router;

$router->group(['prefix' => 'listeners'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.listeners.index',
		'uses' => 'ListenersController@index',
		'middleware' => 'can:manage listeners',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.listeners.cancel',
		'uses' => 'ListenersController@cancel',
	]);
	$router->post('/store', [
		'as'   => 'admin.listeners.store',
		'uses' => 'ListenersController@store',
		'middleware' => 'can:create listeners|edit listeners',
	]);
	$router->get('/publish/{id?}', [
		'as'   => 'admin.listeners.publish',
		'uses' => 'ListenersController@publish',
		'middleware' => 'can:edit.state listeners',
	]);
	$router->get('/unpublish/{id?}', [
		'as'   => 'admin.listeners.unpublish',
		'uses' => 'ListenersController@unpublish',
		'middleware' => 'can:edit.state listeners',
	]);
	$router->get('/{id}', [
		'as'   => 'admin.listeners.edit',
		'uses' => 'ListenersController@edit',
		'middleware' => 'can:edit listeners',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.listeners.delete',
		'uses' => 'ListenersController@delete',
		'middleware' => 'can:delete listeners',
	]);
	$router->post('/reorder', [
		'as'   => 'admin.listeners.reorder',
		'uses' => 'ListenersController@reorder',
	]);
	$router->post('/saveorder', [
		'as'   => 'admin.listeners.saveorder',
		'uses' => 'ListenersController@saveorder',
	]);
	$router->post('/checkin', [
		'as'   => 'admin.listeners.checkin',
		'uses' => 'ListenersController@checkin',
	]);
	$router->post('/batch', [
		'as'   => 'admin.listeners.batch',
		'uses' => 'ListenersController@batch',
		'middleware' => 'can:edit.state listeners',
	]);
});
