<?php

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
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->integer('state')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->text('content');
				$table->text('params')->nullable();
				$table->integer('main')->unsigned()->default(0);
				$table->integer('snippet')->unsigned()->default(0);
				$table->index('state');
				$table->index('access');
				$table->index('snippet');
			});
		}

		if (!Schema::hasTable('kb_page_associations'))
		{
			Schema::create('kb_page_associations', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0)->comment('Parent kb_page_associations.id');
				$table->integer('page_id')->unsigned()->default(0)->comment('FK to kb_pages.id');
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->string('path');
				$table->integer('state')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->index(['parent_id', 'page_id']);
				$table->index('state');
				$table->index('access');
			});
		}

		if (!Schema::hasTable('kb_snippet_associations'))
		{
			Schema::create('kb_snippet_associations', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parent_id')->unsigned()->default(0)->comment('Parent kb_snippet_associations.id');
				$table->integer('page_id')->unsigned()->default(0)->comment('FK to kb_pages.id');
				$table->integer('lft')->unsigned()->default(0);
				$table->integer('rgt')->unsigned()->default(0);
				$table->integer('level')->unsigned()->default(0);
				$table->string('path');
				$table->integer('state')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->index(['parent_id', 'page_id']);
				$table->index('state');
				$table->index('access');
			});
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
			'kb_snippet_associations'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
