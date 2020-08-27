<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('menu_items'))
		{
			Schema::create('menu_items', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('menutype', 24);
				$table->string('title', 255);
				$table->string('alias', 255);
				$table->string('note', 255);
				$table->string('path', 1024);
				$table->string('link', 1024);
				$table->string('type', 16);
				$table->tinyInteger('published')->unsigned()->default(0);
				$table->integer('parent_id')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->integer('module_id')->unsigned()->default(0);
				$table->integer('ordering')->unsigned()->default(0);
				$table->integer('checked_out')->unsigned()->default(0);
				$table->timestamp('checked_out_time')->nullable();
				$table->tinyInteger('browserNav')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->string('img', 255);
				$table->integer('template_style_id')->unsigned()->default(0);
				$table->text('params')->nullable();
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->tinyInteger('home')->unsigned()->default(0);
				$table->string('language', 7);
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->index(['module_id', 'menutype', 'published', 'access']);
				$table->index('menutype');
				$table->index(['lft', 'rgt']);
				$table->index('alias');
				//$table->index('path');
				$table->index('language');
				$table->index(['client_id', 'parent_id', 'alias', 'language']);
			});
		}

		if (!Schema::hasTable('menus'))
		{
			Schema::create('menus', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('menutype', 24);
				$table->string('title', 48);
				$table->string('description', 255);
				$table->index('menutype');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('menus');
		Schema::dropIfExists('menu_items');
	}
}
