<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'groups', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.members',
			'uses' => 'MembersController@index',
		]);
		$router->post('/', [
			'as' => 'api.groups.members.create',
			'uses' => 'MembersController@create',
			'middleware' => ['can:edit groups|edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.members.read',
			'uses' => 'MembersController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.groups.members.update',
			'uses' => 'MembersController@update',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'motd'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.motd',
			'uses' => 'MotdController@index',
		]);
		$router->post('/', [
			'as' => 'api.groups.motd.create',
			'uses' => 'MotdController@create',
			'middleware' => ['can:edit groups|edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.motd.read',
			'uses' => 'MotdController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.groups.motd.update',
			'uses' => 'MotdController@update',
			'middleware' => [ 'can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.motd.delete',
			'uses' => 'MotdController@delete',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'departments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.departments',
			'uses' => 'DepartmentsController@index',
		]);
		$router->post('/', [
			'as' => 'api.groups.departments.create',
			'uses' => 'DepartmentsController@create',
			'middleware' => ['can:manage groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.departments.read',
			'uses' => 'DepartmentsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.groups.departments.update',
			'uses' => 'DepartmentsController@update',
			'middleware' => ['can:manage groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.departments.delete',
			'uses' => 'DepartmentsController@delete',
			'middleware' => ['can:manage groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'fieldsofscience'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.fieldsofscience',
			'uses' => 'FieldsOfScienceController@index',
		]);
		$router->post('/', [
			'as' => 'api.groups.fieldsofscience.create',
			'uses' => 'FieldsOfScienceController@create',
			'middleware' => ['can:manage groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.fieldsofscience.read',
			'uses' => 'FieldsOfScienceController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.groups.fieldsofscience.update',
			'uses' => 'FieldsOfScienceController@update',
			'middleware' => ['can:manage groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.fieldsofscience.delete',
			'uses' => 'FieldsOfScienceController@delete',
			'middleware' => ['can:manage groups'],
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as' => 'api.groups.index',
		'uses' => 'GroupsController@index',
	]);
	$router->post('/', [
		'as' => 'api.groups.create',
		'uses' => 'GroupsController@create',
		'middleware' => ['can:create groups'],
	]);
	$router->get('{id}', [
		'as' => 'api.groups.read',
		'uses' => 'GroupsController@read',
	]);
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.groups.update',
		'uses' => 'GroupsController@update',
		'middleware' => ['can:edit groups|edit.own groups'],
	]);
	$router->delete('{id}', [
		'as' => 'api.groups.delete',
		'uses' => 'GroupsController@delete',
		'middleware' => ['can:delete groups'],
	]);

	$router->group(['prefix' => '{group}/departments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.groupdepartments',
			'uses' => 'GroupDepartmentsController@index',
		]);
		$router->post('/', [
			'as' => 'api.groups.groupdepartments.create',
			'uses' => 'GroupDepartmentsController@create',
			'middleware' => ['can:edit groups|edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.groupdepartments.read',
			'uses' => 'GroupDepartmentsController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.groups.groupdepartments.update',
			'uses' => 'GroupDepartmentsController@update',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.groupdepartments.delete',
			'uses' => 'GroupDepartmentsController@delete',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => '{group}/fieldsofscience'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.groupfieldsofscience',
			'uses' => 'GroupFieldsOfScienceController@index',
		]);
		$router->post('/', [
			'as' => 'api.groups.groupfieldsofscience.create',
			'uses' => 'GroupFieldsOfScienceController@create',
			'middleware' => ['can:edit groups|edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.groupfieldsofscience.read',
			'uses' => 'GroupFieldsOfScienceController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.groups.groupfieldsofscience.update',
			'uses' => 'GroupFieldsOfScienceController@update',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.groupfieldsofscience.delete',
			'uses' => 'GroupFieldsOfScienceController@delete',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
	});
});

$router->group(['prefix' => 'unixgroups', 'middleware' => 'auth:api'], function (Router $router)
{
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.unixgroups.members',
			'uses' => 'UnixGroupMembersController@index',
		]);
		$router->post('/', [
			'as' => 'api.unixgroups.members.create',
			'uses' => 'UnixGroupMembersController@create',
			'middleware' => ['can:edit groups|edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.unixgroups.members.read',
			'uses' => 'UnixGroupMembersController@read',
		])->where('id', '[0-9]+');
		$router->match(['put', 'patch'], '{id}', [
			'as' => 'api.unixgroups.members.update',
			'uses' => 'UnixGroupMembersController@update',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.unixgroups.members.delete',
			'uses' => 'UnixGroupMembersController@delete',
			'middleware' => ['can:edit groups|edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as'   => 'api.unixgroups.index',
		'uses' => 'UnixGroupsController@index',
	]);
	$router->post('/', [
		'as' => 'api.unixgroups.create',
		'uses' => 'UnixGroupsController@create',
		'middleware' => ['can:edit groups|edit.own groups'],
	]);
	$router->get('{id}', [
		'as' => 'api.unixgroups.read',
		'uses' => 'UnixGroupsController@read',
	])->where('id', '[0-9]+');
	$router->match(['put', 'patch'], '{id}', [
		'as' => 'api.unixgroups.update',
		'uses' => 'UnixGroupsController@update',
		'middleware' => ['can:edit groups|edit.own groups'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.unixgroups.delete',
		'uses' => 'UnixGroupsController@delete',
		'middleware' => ['can:edit groups|edit.own groups'],
	])->where('id', '[0-9]+');
});
