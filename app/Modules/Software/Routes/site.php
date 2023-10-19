<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => config('module.software.route', 'software')], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as'   => 'site.software.index',
		'uses' => 'SoftwareController@index',
	]);
	$router->get('/create', [
		'as'   => 'site.software.create',
		'uses' => 'SoftwareController@create',
		'middleware' => ['auth', 'can:create software'],
	]);
	$router->post('/store', [
		'as'   => 'site.software.store',
		'uses' => 'SoftwareController@store',
		'middleware' => ['auth', 'can:create software|edit software'],
	]);
	$router->get('/edit/{id}', [
		'as'   => 'site.software.edit',
		'uses' => 'SoftwareController@edit',
		'middleware' => ['auth', 'can:edit software'],
	])->where('id', '[0-9]+');
	$router->get('/delete/{id}', [
		'as'   => 'site.software.delete',
		'uses' => 'SoftwareController@delete',
		'middleware' => ['auth', 'can:delete software'],
	]);
	$router->get('/{alias}', [
		'as'   => 'site.software.show',
		'uses' => 'SoftwareController@show',
	])->where('alias', '[a-z0-9-_]+');
});
