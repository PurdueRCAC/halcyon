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
				//$table->engine = 'InnoDB';
				$table->increments('id');
				$table->string('title', 100)->default('');
				$table->string('note', 255)->default('');
				$table->text('content');
				$table->integer('ordering')->unsigned()->default(0);
				$table->string('position', 50)->default('');
				$table->integer('checked_out')->unsigned()->default(0);
				$table->timestamp('checked_out_time')->nullable();
				$table->timestamp('publish_up')->nullable();
				$table->timestamp('publish_down')->nullable();
				$table->tinyInteger('published')->unsigned()->default(0);
				$table->string('module', 50)->default('');
				$table->integer('access')->unsigned()->default(0);
				$table->tinyInteger('showtitle')->unsigned()->default(0);
				$table->text('params')->nullable();
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->string('language', 7)->default('');
				$table->index(['published', 'access']);
				$table->index(['module', 'published']);
				$table->index('language');
			});
		}

		if (!Schema::hasTable('widgets_menu'))
		{
			Schema::create('widgets_menu', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('moduleid')->unsigned()->default(0);
				$table->integer('menuid')->unsigned()->default(0);
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
