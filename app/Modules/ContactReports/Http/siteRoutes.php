<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'contactreports', 'middleware' => ['can:manage contactreports']], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.contactreports.index',
		'uses' => 'ReportsController@index',
		'middleware' => 'can:tag.tags.index',
	]);

	$router->get('create', [
		'as' => 'site.contactreports.create',
		'uses' => 'ReportsController@create',
		'middleware' => 'can:create contactreports',
	]);
	$router->post('store', [
		'as' => 'site.contactreports.store',
		'uses' => 'ReportsController@store',
		//'middleware' => 'can:tag.tags.create',
	]);

	$router->get('{id}', [
		'as' => 'site.contactreports.show',
		'uses' => 'ReportsController@show',
		'middleware' => 'can:edit contactreports',
	])->where('id', '[0-9]+');

	$router->get('{id}/edit', [
		'as' => 'site.contactreports.edit',
		'uses' => 'ReportsController@edit',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');

	$router->put('{id}', [
		'as' => 'site.contactreports.update',
		'uses' => 'ReportsController@update',
		//'middleware' => 'can:tag.tags.edit',
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'site.contactreports.delete',
		'uses' => 'ReportsController@delete',
		'middleware' => 'can:delete contactreports',
	])->where('id', '[0-9]+');
});
