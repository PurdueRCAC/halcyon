<?php
// [!] Legacy compatibility
use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'unixgroup', 'middleware' => 'auth.ip'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.unixgroup.usage',
		'uses' => 'UnixGroupsController@index',
	]);
	$router->post('/', [
		'as' => 'ws.unixgroup.create',
		'uses' => 'UnixGroupsController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.unixgroup.read',
		'uses' => 'UnixGroupsController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'post'], '{id}', [
		'as' => 'ws.unixgroupe.update',
		'uses' => 'UnixGroupsController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.unixgroup.delete',
		'uses' => 'UnixGroupsController@delete',
	])->where('id', '[0-9]+');
});
