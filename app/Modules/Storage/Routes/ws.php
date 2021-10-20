<?php
// [!] Legacy compatibility
use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'storagedirusage', 'middleware' => 'auth.ip'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.storage.usage',
		'uses' => 'UsageController@index',
	]);
	$router->post('/', [
		'as' => 'ws.storage.usage.create',
		'uses' => 'UsageController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.storage.usage.read',
		'uses' => 'UsageController@read',
	])->where('id', '[0-9]+');
	$router->get('{search}', [
		'as' => 'ws.storage.usage',
		'uses' => 'UsageController@index',
	])->where('id', '[a-zA-Z_]+');
	$router->match(['put', 'post'], '{id}', [
		'as' => 'ws.storage.usage.update',
		'uses' => 'UsageController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.storage.usage.delete',
		'uses' => 'UsageController@delete',
	])->where('id', '[0-9]+');
});

$router->get('storagedirquota/{username?}', [
	'as' => 'ws.storage.quotas',
	'uses' => 'QuotasController@index',
	'middleware' => ['auth.ip', 'throttle:360,10']
]);

$router->group(['prefix' => 'storagedirpurchase', 'middleware' => 'auth.ip'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.storage.purchases',
		'uses' => 'PurchasesController@index',
	]);
	$router->post('/', [
		'as' => 'ws.storage.purchases.create',
		'uses' => 'PurchasesController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.storage.purchases.read',
		'uses' => 'PurchasesController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'post'], '{id}', [
		'as' => 'ws.storage.purchases.update',
		'uses' => 'PurchasesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.storage.purchases.delete',
		'uses' => 'PurchasesController@delete',
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'storagedirloan', 'middleware' => 'auth.ip'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.storage.loans',
		'uses' => 'LoansController@index',
	]);
	$router->post('/', [
		'as' => 'ws.storage.loans.create',
		'uses' => 'LoansController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.storage.loans.read',
		'uses' => 'LoansController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'post'], '{id}', [
		'as' => 'ws.storage.loans.update',
		'uses' => 'LoansController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.storage.loans.delete',
		'uses' => 'LoansController@delete',
	])->where('id', '[0-9]+');
});

$router->group(['prefix' => 'storagedir', 'middleware' => 'auth.ip'], function (Router $router)
{
	$router->get('/', [
		'as' => 'ws.storage.directories',
		'uses' => 'DirectoriesController@index',
	]);
	$router->post('/', [
		'as' => 'ws.storage.directories.create',
		'uses' => 'DirectoriesController@create',
	]);
	$router->get('{id}', [
		'as' => 'ws.storage.directories.read',
		'uses' => 'DirectoriesController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'post'], '{id}', [
		'as' => 'ws.storage.directories.update',
		'uses' => 'DirectoriesController@update',
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'ws.storage.directories.delete',
		'uses' => 'DirectoriesController@delete',
	])->where('id', '[0-9]+');
});
