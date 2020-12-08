<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'groups'], function (Router $router)
{
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.members',
			'uses' => 'MembersController@index',
			'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.members.create',
			'uses' => 'MembersController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.members.read',
			'uses' => 'MembersController@read',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.members.update',
			'uses' => 'MembersController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'motd'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.motd',
			'uses' => 'MotdController@index',
			'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.motd.create',
			'uses' => 'MotdController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.motd.read',
			'uses' => 'MotdController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.motd.update',
			'uses' => 'MotdController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.motd.delete',
			'uses' => 'MotdController@delete',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'departments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.departments',
			'uses' => 'DepartmentsController@index',
			//'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.departments.create',
			'uses' => 'DepartmentsController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.departments.read',
			'uses' => 'DepartmentsController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.departments.update',
			'uses' => 'DepartmentsController@update',
			'middleware' => ['auth:api', 'can:edit groups.departments'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.departments.delete',
			'uses' => 'DepartmentsController@delete',
			'middleware' => ['auth:api', 'can:delete groups.departments'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => 'fieldsofscience'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.fieldsofscience',
			'uses' => 'FieldsOfScienceController@index',
			//'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.fieldsofscience.create',
			'uses' => 'FieldsOfScienceController@create',
			'middleware' => ['auth:api', 'can:edit groups.fieldsofscience'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.fieldsofscience.read',
			'uses' => 'FieldsOfScienceController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.fieldsofscience.update',
			'uses' => 'FieldsOfScienceController@update',
			'middleware' => ['auth:api', 'can:edit groups.fieldsofscience'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.fieldsofscience.delete',
			'uses' => 'FieldsOfScienceController@delete',
			'middleware' => ['auth:api', 'can:delete groups.fieldsofscience'],
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as' => 'api.groups.index',
		'uses' => 'GroupsController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'api.groups.create',
		'uses' => 'GroupsController@create',
		'middleware' => ['auth:api', 'can:create groups'],
	]);
	$router->get('{id}', [
		'as' => 'api.groups.read',
		'uses' => 'GroupsController@read',
		'middleware' => 'auth:api',
	]);
	$router->put('{id}', [
		'as' => 'api.groups.update',
		'uses' => 'GroupsController@update',
		'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
	]);
	$router->delete('{id}', [
		'as' => 'api.groups.delete',
		'uses' => 'GroupsController@delete',
		'middleware' => ['auth:api', 'can:delete groups'],
	]);

	$router->group(['prefix' => '{group}/departments'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.groupdepartments',
			'uses' => 'GroupDepartmentsController@index',
			//'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.groupdepartments.create',
			'uses' => 'GroupDepartmentsController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.groupdepartments.read',
			'uses' => 'GroupDepartmentsController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.groupdepartments.update',
			'uses' => 'GroupDepartmentsController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.groupdepartments.delete',
			'uses' => 'GroupDepartmentsController@delete',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->group(['prefix' => '{group}/fieldsofscience'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.groups.groupfieldsofscience',
			'uses' => 'GroupFieldsOfScienceController@index',
			//'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.groups.groupfieldsofscience.create',
			'uses' => 'GroupFieldsOfScienceController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.groups.groupfieldsofscience.read',
			'uses' => 'GroupFieldsOfScienceController@read',
			//'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.groups.groupfieldsofscience.update',
			'uses' => 'GroupFieldsOfScienceController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.groups.groupfieldsofscience.delete',
			'uses' => 'GroupFieldsOfScienceController@delete',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
	});
});

$router->group(['prefix' => 'unixgroups'], function (Router $router)
{
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->get('/', [
			'as'   => 'api.unixgroups.members',
			'uses' => 'UnixGroupMembersController@index',
			'middleware' => 'auth:api',
		]);
		$router->post('/', [
			'as' => 'api.unixgroups.members.create',
			'uses' => 'UnixGroupMembersController@create',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		]);
		$router->get('{id}', [
			'as' => 'api.unixgroups.members.read',
			'uses' => 'UnixGroupMembersController@read',
			'middleware' => 'auth:api',
		])->where('id', '[0-9]+');
		$router->put('{id}', [
			'as' => 'api.unixgroups.members.update',
			'uses' => 'UnixGroupMembersController@update',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
		$router->delete('{id}', [
			'as' => 'api.unixgroups.members.delete',
			'uses' => 'UnixGroupMembersController@delete',
			'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
		])->where('id', '[0-9]+');
	});

	$router->get('/', [
		'as'   => 'api.unixgroups.index',
		'uses' => 'UnixGroupsController@index',
		'middleware' => 'auth:api',
	]);
	$router->post('/', [
		'as' => 'api.unixgroups.create',
		'uses' => 'UnixGroupsController@create',
		'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
	]);
	$router->get('{id}', [
		'as' => 'api.unixgroups.read',
		'uses' => 'UnixGroupsController@read',
		'middleware' => 'auth:api',
	])->where('id', '[0-9]+');
	$router->put('{id}', [
		'as' => 'api.unixgroups.update',
		'uses' => 'UnixGroupsController@update',
		'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
	])->where('id', '[0-9]+');
	$router->delete('{id}', [
		'as' => 'api.unixgroups.delete',
		'uses' => 'UnixGroupsController@delete',
		'middleware' => ['auth:api', 'can:edit groups,edit.own groups'],
	])->where('id', '[0-9]+');
});
