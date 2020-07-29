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
				$table->timestamp('created_at');
				$table->integer('created_by')->unsigned()->default(0);
				$table->timestamp('deleted_at');
				$table->integer('deleted_by')->unsigned()->default(0);
				$table->timestamp('checked_out');
				$table->integer('checked_out_by')->unsigned()->default(0);
				$table->timestamp('publish_up');
				$table->timestamp('publish_down');
				$table->integer('parent_id')->unsigned()->default(0);
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->string('path', 255);
				$table->string('language', 7);
				$table->integer('asset_id')->unsigned()->default(0);
				//$table->integer('version_id')->unsigned()->default(0);
				$table->integer('hits')->unsigned()->default(0);
				$table->integer('length')->unsigned()->default(0);
				$table->text('params');
				$table->text('metakey');
				$table->text('metadesc');
				$table->text('metadata');
			});

			/*Schema::create('page_versions', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('page_id')->unsigned()->default(0);
				$table->integer('version')->unsigned()->default(0);
				$table->string('title', 255);
				$table->text('content');
				$table->timestamp('created_at');
				$table->integer('created_by')->unsigned()->default(0);
				$table->timestamp('removed');
				$table->integer('removed_by')->unsigned()->default(0);
				$table->integer('length')->unsigned()->default(0);
				$table->text('metakey');
				$table->text('metadesc');
				$table->text('metadata');
			});*/

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
					'params'     => '{}'
					//'version_id' => 1
				]);

				/*DB::table('page_versionss')->insert([
					'page_id' => 1,
					'version' => 1,
					'title'   => 'Home',
					'content' => 'Welcome!'
				]);*/
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
		//Schema::dropIfExists('page_versions');
	}
}
