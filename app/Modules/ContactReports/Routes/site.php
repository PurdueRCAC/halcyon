<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'contactreports', 'middleware' => ['auth', 'can:manage contactreports']], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.contactreports.index',
		'uses' => 'ReportsController@index',
	]);

	$router->get('create', [
		'as' => 'site.contactreports.create',
		'uses' => 'ReportsController@create',
		'middleware' => 'can:create contactreports',
	]);
	$router->post('store', [
		'as' => 'site.contactreports.store',
		'uses' => 'ReportsController@store',
	]);

	$router->get('{id}', [
		'as' => 'site.contactreports.show',
		'uses' => 'ReportsController@show',
		'middleware' => 'can:edit contactreports',
	])->where('id', '[0-9]+');

	$router->get('{id}/edit', [
		'as' => 'site.contactreports.edit',
		'uses' => 'ReportsController@edit',
		'middleware' => 'can:edit contactreports',
	])->where('id', '[0-9]+');

	$router->put('{id}', [
		'as' => 'site.contactreports.update',
		'uses' => 'ReportsController@update',
		'middleware' => 'can:edit contactreports|can:edit contactreports',
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'site.contactreports.delete',
		'uses' => 'ReportsController@delete',
		'middleware' => 'can:delete contactreports',
	])->where('id', '[0-9]+');
});
