<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
				$table->string('name', 255);
				$table->tinyInteger('puid')->unsigned()->default(0);
				$table->index('name');
				$table->index('puid');
			});
		}

		if (!Schema::hasTable('userusernames'))
		{
			Schema::create('userusernames', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('Foreign Key to users.id');
				$table->string('username', 16);
				$table->integer('unixid')->unsigned()->default(0);
				$table->dateTime('datecreated');
				$table->dateTime('dateremoved');
				$table->dateTime('datelastseen');
				$table->index(['username', 'datecreated', 'dateremoved'], 'login');
				$table->index(['unixid', 'datecreated', 'dateremoved'], 'unixid');
				$table->index('userid');
				$table->index('datelastseen');
			});
		}

		if (!Schema::hasTable('user_notes'))
		{
			Schema::create('user_notes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0)->comment('Foreign Key to users.id');
				//$table->integer('category_id')->unsigned()->default(0);
				$table->string('subject', 100)->default('');
				$table->text('body');
				$table->tinyInteger('state')->unsigned()->default(0);
				$table->integer('checked_out')->unsigned()->default(0);
				$table->dateTime('checked_out_time');
				$table->integer('created_by')->unsigned()->default(0);
				$table->dateTime('created_at');
				$table->integer('updated_by')->unsigned()->default(0);
				$table->dateTime('updated_at');
				$table->dateTime('review_time');
				//$table->dateTime('publish_up');
				//$table->dateTime('publish_down');
				$table->index('user_id');
				$table->index('category_id');

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
