<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateUsersTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('users'))
		{
			Schema::create('users', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 128);
				$table->integer('puid')->unsigned()->default(0);
				$table->index('name');
				$table->index('puid');
			});

			DB::table('users')->insert([
				'name' => 'Administrator',
			]);
		}

		$admin = DB::table('users')->where('name', '=', 'Administrator')->first();

		if (!Schema::hasTable('userusernames'))
		{
			Schema::create('userusernames', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('Foreign Key to users.id');
				$table->string('username', 16);
				$table->string('email', 255);
				$table->integer('unixid')->unsigned()->default(0);
				$table->dateTime('datecreated')->nullable();
				$table->dateTime('dateremoved')->nullable();
				$table->dateTime('datelastseen')->nullable();
				$table->index(['username', 'datecreated', 'dateremoved'], 'login');
				$table->index(['unixid', 'datecreated', 'dateremoved'], 'unixid');
				$table->index('userid');
				$table->index('datelastseen');
			});

			if ($admin)
			{
				DB::table('userusernames')->insert([
					'userid' => $admin->id,
					'username' => 'admin',
					'datecreated' => Carbon::now()->toDateTimeString()
				]);
			}
		}

		if (!Schema::hasTable('user_notes'))
		{
			Schema::create('user_notes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0)->comment('Foreign Key to users.id');
				//$table->string('subject', 100)->default('');
				$table->text('body');
				//$table->string('body', 5000);
				$table->integer('created_by')->unsigned()->default(0);
				$table->dateTime('created_at')->nullable();
				$table->integer('updated_by')->unsigned()->default(0);
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('user_id');

				//$table->foreign('user_id')->references('id')->on('users');
			});
		}

		if (!Schema::hasTable('user_facets'))
		{
			Schema::create('user_facets', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0)->comment('Foreign Key to users.id');
				$table->string('key', 255);
				$table->string('value', 8096);
				$table->tinyInteger('locked')->unsigned()->default(0);
				$table->tinyInteger('access')->unsigned()->default(0);
				$table->index('user_id');
			});
		}

		if (!Schema::hasTable('user_roles'))
		{
			Schema::create('user_roles', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0)->comment('Adjacency List Reference Id');
				$table->integer('lft')->unsigned()->default(0)->comment('Nested set lft.');
				$table->integer('rgt')->unsigned()->default(0)->comment('Nested set rgt.');
				$table->string('title', 100)->default('');
				$table->unique(['parent_id', 'title']);
				$table->index('parent_id');
				$table->index('title');
				$table->index(['lft', 'rgt']);
			});

			$roles = array(
				array(
					'id' => 1,
					'parent_id' => 0,
					'lft' => 0,
					'rgt' => 8,
					'title' => 'Public'
				),
				array(
					'id' => 2,
					'parent_id' => 1,
					'lft' => 2,
					'rgt' => 5,
					'title' => 'Registered'
				),
				array(
					'id' => 3,
					'parent_id' => 2,
					'lft' => 3,
					'rgt' => 4,
					'title' => 'Staff'
				),
				array(
					'id' => 4,
					'parent_id' => 1,
					'lft' => 6,
					'rgt' => 7,
					'title' => 'Super Users'
				)
			);

			foreach ($roles as $role)
			{
				DB::table('user_roles')->insert($role);
			}
		}

		if (!Schema::hasTable('user_role_map'))
		{
			Schema::create('user_role_map', function (Blueprint $table)
			{
				//$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0)->comment('Foreign Key to users.id');
				$table->integer('role_id')->unsigned()->default(0)->comment('Foreign Key to user_roles.id');
				$table->primary(['user_id', 'role_id']);

				$table->foreign('user_id')->references('id')->on('users');
			});

			if ($admin)
			{
				$super = DB::table('user_roles')->where('title', '=', 'Super Users')->first();

				if ($super)
				{
					DB::table('user_role_map')->insert([
						'user_id' => $admin->id,
						'role_id' => $super->id
					]);
				}
			}
		}

		if (!Schema::hasTable('permissions'))
		{
			Schema::create('permissions', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0)->comment('Nested set parent.');
				$table->integer('lft')->unsigned()->default(0)->comment('Nested set lft.');
				$table->integer('rgt')->unsigned()->default(0)->comment('Nested set rgt.');
				$table->integer('level')->unsigned()->default(0)->comment('The cached level in the nested tree.');
				$table->string('name', 50)->comment('The unique name for the asset.');
				$table->string('title', 100)->comment('The descriptive title for the asset.');
				$table->string('rules', 5120)->comment('JSON encoded access control.');
				$table->unique('name');
				$table->index(['lft', 'rgt']);
				$table->index('parent_id');
			});

			DB::table('permissions')->insert([
				'parent_id' => 0,
				'lft' => 0,
				'rgt' => 1,
				'level' => 0,
				'name' => 'root.1',
				'title' => 'Root Asset',
				'rules' => '{"login.site":{"1":1,"2":1},"admin":{"4":1},"manage":{"3":1},"create":{"3":1},"delete":{"3":1},"edit":{"3":1},"edit.state":{"3":1},"edit.own":{"2":1,"3":1}}'
			]);
		}

		if (!Schema::hasTable('viewlevels'))
		{
			Schema::create('viewlevels', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 100)->comment('The descriptive title for the entry.');
				$table->integer('ordering')->unsigned()->default(0);
				$table->string('rules', 5120)->comment('JSON encoded access control.');
				$table->unique('title');
			});

			$levels = array(
				array(
					'id' => 1,
					'title' => 'Public',
					'ordering' => 0,
					'rules' => '[1]'
				),
				array(
					'id' => 2,
					'title' => 'Registered',
					'ordering' => 1,
					'rules' => '[2,4]'
				),
				array(
					'id' => 3,
					'title' => 'Staff',
					'ordering' => 2,
					'rules' => '[3,4]'
				),
				array(
					'id' => 4,
					'title' => 'Administrators',
					'ordering' => 3,
					'rules' => '[4]'
				)
			);

			foreach ($levels as $level)
			{
				DB::table('viewlevels')->insert($level);
			}
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$tables = array(
			'users',
			'user_notes',
			'user_facets',
			'user_roles',
			'user_role_map',
			'permissions',
			'viewlevels'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
