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
		'as' => 'ws.unixgroup.update',
		'uses' => 'UnixGroupsController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.unixgroup.delete',
		'uses' => 'UnixGroupsController@delete',
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'groupmotd'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.groupmotd.usage',
		'uses' => 'MotdController@index',
	]);
	$router->post('/', [
		'as' => 'ws.groupmotd.create',
		'uses' => 'MotdController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.groupmotd.read',
		'uses' => 'MotdController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'post'], '{id}', [
		'as' => 'ws.groupmotd.update',
		'uses' => 'MotdController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.groupmotd.delete',
		'uses' => 'MotdController@delete',
	])->where('id', '[0-9]+');
});
