<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('/', [
	'uses' => 'PagesController@index',
	'as' => 'home',
]);

$router->group(['prefix' => 'pages'], function (Router $router)
{
	$router->get('/create', [
		'as'   => 'site.pages.create',
		'uses' => 'PagesController@create',
		'middleware' => 'can:create pages',
	]);
	$router->post('/store', [
		'as'   => 'site.pages.store',
		'uses' => 'PagesController@store',
		'middleware' => 'can:create pages,edit pages',
	]);
	$router->match(['get', 'post'], '/publish/{id?}', [
		'as'   => 'site.pages.publish',
		'uses' => 'PagesController@state',
		'middleware' => 'can:edit.state pages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/unpublish/{id?}', [
		'as'   => 'site.pages.unpublish',
		'uses' => 'PagesController@state',
		'middleware' => 'can:edit.state pages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/restore/{id?}', [
		'as'   => 'site.pages.restore',
		'uses' => 'PagesController@restore',
		'middleware' => 'can:edit.state pages',
	])->where('id', '[0-9]+');
	$router->get('/{id}', [
		'as'   => 'site.pages.edit',
		'uses' => 'PagesController@edit',
		'middleware' => 'can:edit pages',
	])->where('id', '[0-9]+');
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'site.pages.delete',
		'uses' => 'PagesController@delete',
		'middleware' => 'can:delete pages',
	]);
});

// This rule will catch anything else. Due to this
// it needs to be last which, in turn, means the
// module must be loaded last. This is controlled by
// the "priority" option in the `module.json` file.
//
// TODO: Move to `$router->fallback()`?
$router->match(['get', 'post'],'{uri}', [
	'uses' => 'PagesController@index',
	'as' => 'page',
])->where('uri', '(.*)');
