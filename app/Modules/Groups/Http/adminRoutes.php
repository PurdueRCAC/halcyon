<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'groups'], function (Router $router)
{
	$router->match(['get', 'post'], '/', [
		'as' => 'admin.groups.index',
		'uses' => 'GroupsController@index',
		'middleware' => 'can:manage groups',
	]);
	$router->get('create', [
		'as' => 'admin.groups.create',
		'uses' => 'GroupsController@create',
		'middleware' => 'can:create groups',
	]);
	$router->post('store', [
		'as' => 'admin.groups.store',
		'uses' => 'GroupsController@store',
		'middleware' => 'can:create groups,edit groups',
	]);
	$router->get('edit/{id}', [
		'as' => 'admin.groups.edit',
		'uses' => 'GroupsController@edit',
		'middleware' => 'can:edit groups',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.groups.delete',
		'uses' => 'GroupsController@delete',
		'middleware' => 'can:delete groups',
	]);
	$router->match(['get', 'post'], 'cancel', [
		'as' => 'admin.groups.cancel',
		'uses' => 'GroupsController@cancel',
	]);

	// Members
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->match(['get', 'post'], '/{group}', [
			'as'   => 'admin.groups.members',
			'uses' => 'MembersController@index',
			'middleware' => 'can:manage groups',
		]);
		$router->get('/create', [
			'as' => 'admin.groups.members.create',
			'uses' => 'MembersController@create',
			'middleware' => 'can:create groups.members',
		]);
		$router->post('/store', [
			'as' => 'admin.groups.members.store',
			'uses' => 'MembersController@store',
			'middleware' => 'can:create groups.members,edit groups.members',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.groups.members.edit',
			'uses' => 'MembersController@edit',
			'middleware' => 'can:edit groups.members',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.groups.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => 'can:delete groups.members',
		]);
		$router->post('/cancel', [
			'as' => 'admin.groups.members.cancel',
			'uses' => 'MembersController@cancel',
		]);
	});

	$router->group(['prefix' => 'fieldsofscience'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.groups.fieldsofscience',
			'uses' => 'FieldsOfScienceController@index',
			'middleware' => 'can:manage groups.fieldsofscience',
		]);
		$router->get('/create', [
			'as' => 'admin.groups.fieldsofscience.create',
			'uses' => 'FieldsOfScienceController@create',
			'middleware' => 'can:create groups.fieldsofscience',
		]);
		$router->post('/store', [
			'as' => 'admin.groups.fieldsofscience.store',
			'uses' => 'FieldsOfScienceController@store',
			'middleware' => 'can:create groups.fieldsofscience,edit groups.fieldsofscience',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.groups.fieldsofscience.edit',
			'uses' => 'FieldsOfScienceController@edit',
			'middleware' => 'can:edit groups.fieldsofscience',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.groups.fieldsofscience.delete',
			'uses' => 'FieldsOfScienceController@delete',
			'middleware' => 'can:delete groups.fieldsofscience',
		]);
		$router->post('/cancel', [
			'as' => 'admin.groups.fieldsofscience.cancel',
			'uses' => 'FieldsOfScienceController@cancel',
		]);
	});
});
