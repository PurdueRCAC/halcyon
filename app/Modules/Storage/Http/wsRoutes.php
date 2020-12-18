<?php
// [!] Legacy compatibility
use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'storagedirusage'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.storage.usage',
		'uses' => 'UsageController@index',
	]);
	$router->post('/', [
		'as' => 'api.storage.usage.create',
		'uses' => 'UsageController@create',
	]);
	$router->get('{id}', [
		'as' => 'api.storage.usage.read',
		'uses' => 'UsageController@read',
	])->where('id', '[0-9]+');
	$router->get('{search}', [
		'as' => 'api.storage.usage',
		'uses' => 'UsageController@index',
	])->where('id', '[a-zA-Z_]+');
	$router->put('{id}', [
		'as' => 'api.storage.usage.update',
		'uses' => 'UsageController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.storage.usage.delete',
		'uses' => 'UsageController@delete',
	])->where('id', '[0-9]+');
});

$router->get('storagedirquota/{username?}', [
	'as' => 'api.storage.quotas',
	'uses' => 'QuotasController@index',
]);

$router->group(['prefix' => 'storagedirpurchase'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.storage.purchases',
		'uses' => 'PurchasesController@index',
	]);
	$router->post('/', [
		'as' => 'api.storage.purchases.create',
		'uses' => 'PurchasesController@create',
	]);
	$router->get('{id}', [
		'as' => 'api.storage.purchases.read',
		'uses' => 'PurchasesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.storage.purchases.update',
		'uses' => 'PurchasesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.storage.purchases.delete',
		'uses' => 'PurchasesController@delete',
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'storagedirloan'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.storage.loans',
		'uses' => 'LoansController@index',
	]);
	$router->post('/', [
		'as' => 'api.storage.loans.create',
		'uses' => 'LoansController@create',
	]);
	$router->get('{id}', [
		'as' => 'api.storage.loans.read',
		'uses' => 'LoansController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.storage.loans.update',
		'uses' => 'LoansController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.storage.loans.delete',
		'uses' => 'LoansController@delete',
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'storagedir'], function (Router $router)
{
	$router->get('/', [
		'as' => 'api.storage.directories',
		'uses' => 'DirectoriesController@index',
	]);
	$router->post('/', [
		'as' => 'api.storage.directories.create',
		'uses' => 'DirectoriesController@create',
	]);
	$router->get('{id}', [
		'as' => 'api.storage.directories.read',
		'uses' => 'DirectoriesController@read',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.storage.directories.update',
		'uses' => 'DirectoriesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.storage.directories.delete',
		'uses' => 'DirectoriesController@delete',
	])->where('id', '[0-9]+');
});
