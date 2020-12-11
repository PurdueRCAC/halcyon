<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePagesTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('pages'))
		{
			Schema::create('pages', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 255);
				$table->string('alias', 255);
				$table->text('content');
				$table->integer('state')->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->timestamp('created_at')->nullable();
				$table->integer('created_by')->unsigned()->default(0);
				$table->timestamp('deleted_at')->nullable();
				$table->integer('deleted_by')->unsigned()->default(0);
				$table->timestamp('checked_out')->nullable();
				$table->integer('checked_out_by')->unsigned()->default(0);
				$table->timestamp('publish_up')->nullable();
				$table->timestamp('publish_down')->nullable();
				$table->integer('parent_id')->unsigned()->default(0);
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->string('path', 255);
				$table->string('language', 7);
				$table->integer('asset_id')->unsigned()->default(0);
				$table->integer('hits')->unsigned()->default(0);
				$table->integer('length')->unsigned()->default(0);
				$table->text('params')->nullable();
				$table->text('metakey')->nullable();
				$table->text('metadesc')->nullable();
				$table->text('metadata')->nullable();
			});

			$home = DB::table('pages')->where('parent_id', 0)->first();

			if (!$home || !$home->id)
			{
				DB::table('pages')->insert([
					'title'      => 'Home',
					'alias'      => 'home',
					'content'    => '<p>Welcome!</p>',
					'state'      => 1,
					'access'     => 1,
					'parent_id'  => 0,
					'path'       => '',
					'lft'        => 1,
					'rgt'        => 2,
					'level'      => 0,
					'params'     => '[]',
					'language'   => '*',
				]);
			}
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('pages');
	}
}
