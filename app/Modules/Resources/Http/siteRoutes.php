<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('resourcetypes'))
{
	$types = App\Modules\Resources\Models\Type::all();

	foreach ($types as $type)
	{
		$router->get($type->alias, [
			'as' => 'site.resources.type.' . $type->alias,
			'uses' => 'ResourcesController@type',
		]);

		$router->get($type->alias . '/{name}', [
			'as' => 'site.resources.' . $type->alias . '.show',
			'uses' => 'ResourcesController@show',
		])->where('name', '[a-z0-9\-_]+');

		$router->get($type->alias . '/retired', [
			'as' => 'site.resources.' . $type->alias . '.retired',
			'uses' => 'ResourcesController@retired',
		]);
	}
}
/*
$router->group(['prefix' => 'resources'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.resources.index',
		'uses' => 'ResourcesController@index',
	]);
});
*/
