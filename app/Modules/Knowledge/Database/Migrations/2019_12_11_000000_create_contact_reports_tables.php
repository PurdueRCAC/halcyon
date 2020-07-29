<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration script for installing news tables
 **/
class CreateKnowledgeTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('kb_pages'))
		{
			Schema::create('kb_pages', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title');
				$table->string('alias');
				$table->timestamp('created_at');
				$table->timestamp('updated_at');
				$table->timestamp('deleted_at');
				$table->integer('state')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->text('content');
				$table->text('params');
				$table->integer('main')->unsigned()->default(0);
				$table->integer('snippet')->unsigned()->default(0);
				$table->index('state');
				$table->index('access');
				$table->index('snippet');
			});
			$this->info('Created `kb_pages` table.');
		}

		if (!Schema::hasTable('kb_page_associations'))
		{
			Schema::create('kb_page_associations', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0);
				$table->integer('child_id')->unsigned()->default(0);
				$table->integer('ordering')->unsigned()->default(0);
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->index('parent_id');
				$table->index('child_id');
			});
			$this->info('Created `kb_page_associations` table.');
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'kb_pages',
			'kb_page_associations',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
			$this->info('Dropped `' . $table . '` table.');
		}
	}
}
