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
				$table->integer('parent_id')->unsigned()->default(0)->comment('Parent tags.id');
				$table->string('name', 150);
				$table->string('slug', 100);
				$table->string('domain', 100);
				$table->dateTime('created_at')->nullable();
				$table->integer('created_by')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('updated_at')->nullable();
				$table->integer('updated_by')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('deleted_at')->nullable();
				$table->integer('deleted_by')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('tagged_count')->unsigned()->default(0)->comment('Cached count for number of items tagged');
				$table->integer('alias_count')->unsigned()->default(0)->comment('Cached count for number of aliases');
				$table->index('parent_id');
			});
		}

		if (!Schema::hasTable('tags_tagged'))
		{
			Schema::create('tags_tagged', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('tag_id')->unsigned()->default(0)->comment('FK to tags.id');
				$table->integer('taggable_id')->unsigned()->default(0);
				$table->string('taggable_type', 255);
				$table->dateTime('created_at')->nullable();
				$table->integer('created_by')->unsigned()->default(0)->comment('FK to users.id');
				$table->index(['taggable_id', 'taggable_type'], 'taggables');
				$table->index('tag_id');
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
		Schema::dropIfExists('tags');
		Schema::dropIfExists('tags_tagged');
	}
}
