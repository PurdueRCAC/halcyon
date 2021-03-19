<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('tags'))
		{
			Schema::create('tags', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0);
				$table->string('name', 150);
				$table->string('slug', 100);
				$table->string('namespace', 100);
				$table->integer('created_by')->unsigned()->default(0);
				$table->integer('updated_by')->unsigned()->default(0);
				$table->integer('tagged_count')->unsigned()->default(0);
				$table->integer('alias_count')->unsigned()->default(0);
				$table->timestamps();
				$table->index('parent_id');
			});
		}

		if (!Schema::hasTable('tags_tagged'))
		{
			Schema::create('tags_tagged', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('tag_id')->unsigned()->default(0);
				$table->integer('taggable_id')->unsigned()->default(0);
				$table->string('taggable_type', 255);
				$table->timestamp('created_at');
				$table->integer('created_by')->unsigned()->default(0);
				$table->index(['taggable_id', 'taggable_type']);
				$table->index('tag_id');
			});
		}

		/*Schema::create('tags_substitutes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tag_id')->unsigned()->default(0);
			$table->string('name', 255);
			$table->string('slug', 255);
			//$table->timestamp('created');
			//$table->integer('created_by')->unsigned()->default(0);
			$table->timestamps();
			$table->index('tag_id');
		});

		Schema::create('tags_logs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tag_id')->unsigned()->default(0);
			$table->timestamp('created');
			$table->integer('user_id')->unsigned()->default(0);
			$table->string('action', 50);
			$table->text('comments')->nullable();
			$table->index('tag_id');
			$table->index('user_id');
		});*/
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('tags');
		Schema::dropIfExists('tags_tagged');
		//Schema::drop('tags_substitutes');
		//Schema::drop('tags_log');
	}
}
