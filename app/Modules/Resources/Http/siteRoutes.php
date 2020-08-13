<?php

use Illuminate\Routing\Router;

/** @var Router $router */
/*$router->group(['prefix' => 'resources'], function (Router $router) {
	$router->get('tags', [
		'as' => 'admin.tag.tag.index',
		'uses' => 'TagsController@index',
		'middleware' => 'can:tag.tags.index',
	]);
	$router->get('tags/create', [
		'as' => 'admin.tag.tag.create',
		'uses' => 'TagsController@create',
		'middleware' => 'can:tag.tags.create',
	]);
	$router->post('tags', [
		'as' => 'admin.tag.tag.store',
		'uses' => 'TagsController@store',
		'middleware' => 'can:tag.tags.create',
	]);
	$router->get('tags/{tag__tag}/edit', [
		'as' => 'admin.tag.tag.edit',
		'uses' => 'TagsController@edit',
		'middleware' => 'can:tag.tags.edit',
	]);
	$router->put('tags/{tag__tag}', [
		'as' => 'admin.tag.tag.update',
		'uses' => 'TagsController@update',
		'middleware' => 'can:tag.tags.edit',
	]);
	$router->delete('tags/{tag__tag}', [
		'as' => 'admin.tag.tag.destroy',
		'uses' => 'TagsController@destroy',
		'middleware' => 'can:tag.tags.destroy',
	]);
});*/
$types = App\Modules\Resources\Entities\Type::all();
foreach ($types as $type)
{
	$router->get($type->alias, [
		'as' => 'site.resources.type.' . $type->alias,
		'uses' => 'ResourcesController@type',
	]);
	$router->get($type->alias . '/retired', [
		'as' => 'site.resources.' . $type->alias . '.retired',
		'uses' => 'ResourcesController@retired',
	]);
	$router->get($type->alias . '/{name}', [
		'as' => 'site.resources.' . $type->alias . '.show',
		'uses' => 'ResourcesController@show',
	])->where('name', '[a-z0-9\-_]+');
}

$router->get('resources', 'ResourcesController@index');