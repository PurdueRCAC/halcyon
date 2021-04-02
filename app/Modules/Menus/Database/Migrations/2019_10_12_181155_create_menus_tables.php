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
				$table->string('menutype', 24)->comment('The type of menu this item belongs to. FK to menus.menutype.');
				$table->string('title', 255);
				$table->string('alias', 255)->comment('The SEF alias of the menu item.');
				$table->string('note', 255);
				$table->string('path', 1024)->comment('The computed path of the menu item based on the alias field.');
				$table->string('link', 1024)->comment('The actually link the menu item refers to.');
				$table->string('type', 16)->comment('The type of link: Page, URL, Separator');
				$table->tinyInteger('published')->unsigned()->default(0)->comment('The published state of the menu link.');
				$table->integer('parent_id')->unsigned()->default(0)->comment('The parent menu item in the menu tree.');
				$table->integer('level')->unsigned()->default(0)->comment('The relative level in the tree.');
				$table->integer('module_id')->unsigned()->default(0)->comment('FK to extensions.id');
				$table->integer('ordering')->unsigned()->default(0);
				$table->integer('checked_out')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('checked_out_time')->nullable();
				$table->tinyInteger('target')->unsigned()->default(0)->comment('The click behaviour of the link.');
				$table->integer('access')->unsigned()->default(0)->comment('The access level required to view the menu item.');
				$table->string('class', 255);
				$table->text('params')->nullable()->comment('JSON encoded data for the menu item.');
				$table->integer('lft')->unsigned()->default(0)->comment('Nested set lft.');
				$table->integer('rgt')->unsigned()->default(0)->comment('Nested set rgt.');
				$table->tinyInteger('home')->unsigned()->default(0);
				$table->string('language', 7);
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->dateTime('created_at');
				$table->dateTime('updated_at');
				$table->dateTime('deleted_at');
				$table->index(['module_id', 'menutype', 'published', 'access'], 'module');
				$table->index('menutype');
				$table->index(['lft', 'rgt']);
				$table->index('alias');
				//$table->index('path');
				$table->index('language');
				$table->index(['client_id', 'parent_id', 'alias', 'language'], 'client');
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
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->dateTime('created_at');
				$table->dateTime('updated_at');
				$table->dateTime('deleted_at');
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
