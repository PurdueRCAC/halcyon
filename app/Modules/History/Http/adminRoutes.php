<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'history'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'admin.history.index',
		'uses' => 'HistoryController@index',
		'middleware' => 'can:manage history',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.history.cancel',
		'uses' => 'HistoryController@cancel',
	]);
	$router->post('/store', [
		'as' => 'admin.history.store',
		'uses' => 'HistoryController@store',
		'middleware' => 'can:create history,edit history',
	]);
	$router->get('/{id}', [
		'as' => 'admin.history.show',
		'uses' => 'HistoryController@show',
		//'middleware' => 'can:edit history',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.history.delete',
		'uses' => 'HistoryController@delete',
		'middleware' => 'can:delete history',
	]);
});
