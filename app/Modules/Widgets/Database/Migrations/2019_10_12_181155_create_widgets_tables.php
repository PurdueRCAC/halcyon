<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateWidgetsTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('widgets'))
		{
			Schema::create('widgets', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 100);
				$table->string('note', 255);
				$table->text('content');
				$table->integer('ordering')->unsigned()->default(0);
				$table->string('position', 50);
				$table->integer('checked_out')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('checked_out_time')->nullable();
				$table->dateTime('publish_up')->nullable();
				$table->dateTime('publish_down')->nullable();
				$table->tinyInteger('published')->unsigned()->default(0);
				$table->string('widget', 50);
				$table->integer('access')->unsigned()->default(0);
				$table->tinyInteger('showtitle')->unsigned()->default(0);
				$table->text('params')->nullable();
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->string('language', 7);
				$table->index(['published', 'access'], 'published');
				$table->index(['widget', 'published'], 'widget');
				$table->index('language');
			});
		}

		if (!Schema::hasTable('widgets_menu'))
		{
			Schema::create('widgets_menu', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('widgetid')->unsigned()->default(0)->comment('FK to widgets.id');
				$table->integer('menuid')->unsigned()->default(0)->comment('FK to menus.id');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('widgets');
		Schema::dropIfExists('widgets_menu');
	}
}
