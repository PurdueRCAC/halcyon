<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'knowledge'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.knowledge.index',
		'uses' => 'PagesController@index',
	]);

	$router->get('search', [
		'as' => 'site.knowledge.search',
		'uses' => 'PagesController@search',
	]);

	$router->get('/create', [
		'as' => 'site.knowledge.create',
		'uses' => 'PagesController@create',
		'middleware' => 'can:create knowledge',
	]);
	$router->get('/select', [
		'as' => 'site.knowledge.select',
		'uses' => 'PagesController@select',
		'middleware' => 'can:create knowledge',
	]);
	$router->post('/attach', [
		'as' => 'site.knowledge.attach',
		'uses' => 'PagesController@attach',
		'middleware' => 'can:create knowledge|edit knowledge',
	]);
	$router->post('/restore', [
		'as' => 'site.knowledge.restore',
		'uses' => 'PagesController@restore',
		'middleware' => 'can:edit knowledge',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'site.knowledge.delete',
		'uses' => 'PagesController@delete',
		'middleware' => 'can:delete knowledge',
	]);

	$router->get('{uri}', [
		'as' => 'site.knowledge.page',
		'uses' => 'PagesController@index',
	])->where('uri', '(.*)');
});
