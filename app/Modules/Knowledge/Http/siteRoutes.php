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

	$router->get('{uri}', [
		'as' => 'site.knowledge.page',
		'uses' => 'PagesController@index',
	])->where('uri', '(.*)');
});
