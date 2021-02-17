<?php
// [!] Legacy compatibility
use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'messagequeue'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'ws.messages.index',
		'uses' => 'MessagesController@index',
	]);
	$router->post('/', [
		'as' => 'ws.messages.create',
		'uses' => 'MessagesController@create',
	]);
	$router->get('{id}', [
		'as'   => 'ws.messages.read',
		'uses' => 'MessagesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as'   => 'ws.messages.update',
		'uses' => 'MessagesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.messages.delete',
		'uses' => 'MessagesController@delete',
	])->where('id', '[0-9]+');
});

// Types
$router->group(['prefix' => 'messagequeuetype'], function (Router $router)
{
	$router->get('/', [
		'as'   => 'ws.messages.types',
		'uses' => 'TypesController@index',
	]);
	$router->post('/', [
		'as' => 'ws.messages.types.create',
		'uses' => 'TypesController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.messages.types.read',
		'uses' => 'TypesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'ws.messages.types.update',
		'uses' => 'TypesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.messages.types.delete',
		'uses' => 'TypesController@delete',
	])->where('id', '[0-9]+');
});
