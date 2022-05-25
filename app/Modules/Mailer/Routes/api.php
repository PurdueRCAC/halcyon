<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'mail', 'middleware' => ['auth:api']], function (Router $router)
{
	$router->get('/', [
		'as'   => 'api.mailer.index',
		'uses' => 'MessagesController@index',
	]);

	$router->post('/', [
		'as' => 'api.mailer.create',
		'uses' => 'MessagesController@create',
		'middleware' => ['can:delete mailer']
	]);

	$router->get('{id}', [
		'as'   => 'api.mailer.read',
		'uses' => 'MessagesController@read',
	])->where('id', '[0-9]+');

	$router->match(['put', 'patch'], '{id}', [
		'as'   => 'api.mailer.update',
		'uses' => 'MessagesController@update',
		'middleware' => ['can:edit mailer']
	])->where('id', '[0-9]+');

	$router->delete('{id}', [
		'as' => 'api.mailer.delete',
		'uses' => 'MessagesController@delete',
		'middleware' => ['can:delete mailer']
	])->where('id', '[0-9]+');

	$router->post('/send', [
		'as' => 'api.mailer.send',
		'uses' => 'MessagesController@send',
		'middleware' => ['can:create mailer']
	]);
});
