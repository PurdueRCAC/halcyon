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
				$table->string('given_name', 255);
				$table->string('middle_name', 255);
				$table->string('surname', 255);
				$table->string('username', 150);
				$table->string('email', 255);
				$table->timestamp('email_verified_at');
				$table->string('password', 255);
				$table->tinyInteger('block')->unsigned()->default(0);
				$table->tinyInteger('approved')->unsigned()->default(0);
				$table->timestamp('created_at');
				$table->string('created_ip', 40);
				$table->timestamp('last_visit');
				$table->tinyInteger('activation')->default(0);
				$table->text('params');
				$table->timestamp('updated_at');
				$table->timestamp('deleted_at');
				$table->tinyInteger('organization_id')->unsigned()->default(0);
				$table->string('remember_token', 100);
				$table->index('username');
				$table->index('name');
				$table->index('block');
				$table->index('email');
			});
			$this->info('Created `users` table.');
		}

		if (!Schema::hasTable('user_notes'))
		{
			Schema::create('user_notes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0);
				$table->integer('category_id')->unsigned()->default(0);
				$table->string('subject', 100)->default('');
				$table->text('body');
				$table->tinyInteger('state')->unsigned()->default(0);
				$table->integer('checked_out')->unsigned()->default(0);
				$table->timestamp('checked_out_time');
				$table->integer('created_by')->unsigned()->default(0);
				$table->timestamp('created_at');
				$table->integer('updated_by')->unsigned()->default(0);
				$table->timestamp('updated_at');
				$table->timestamp('review_time');
				$table->timestamp('publish_up');
				$table->timestamp('publish_down');
				$table->index('user_id');
				$table->index('category_id');

				$table->foreign('user_id')->references('id')->on('users');
			});
			$this->info('Created `user_notes` table.');
		}

		if (!Schema::hasTable('user_roles'))
		{
			Schema::create('user_roles', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0);
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->string('title', 100)->default('');
				$table->index(['parent_id', 'title']);
				$table->index('parent_id');
				$table->index('title');
				$table->index(['lft', 'rgt']);
			});
			$this->info('Created `user_roles` table.');
		}

		if (!Schema::hasTable('user_role_map'))
		{
			Schema::create('user_role_map', function (Blueprint $table)
			{
				//$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0);
				$table->integer('role_id')->unsigned()->default(0);

				$table->foreign('user_id')->references('id')->on('users');
			});
			$this->info('Created `user_role_map` table.');
		}

		if (!Schema::hasTable('permissions'))
		{
			Schema::create('permissions', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0);
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->string('name', 50);
				$table->string('title', 100);
				$table->string('rules', 5120);
				$table->index('name');
				$table->index(['lft', 'rgt']);
				$table->index('parent_id');
			});
			$this->info('Created `permissions` table.');
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
			'user_roles',
			'user_role_map',
			'permissions',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
			$this->info('Dropped `' . $table . '` table.');
		}
	}
}
