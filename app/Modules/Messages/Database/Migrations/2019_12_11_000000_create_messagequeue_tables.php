<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing news tables
 **/
class CreateMessagequeueTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('messagequeue'))
		{
			Schema::create('messagequeue', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('messagequeuetypeid')->unsigned()->default(0);
				$table->integer('targetobjectid')->unsigned()->default(0);
				$table->integer('messagequeueoptionsid')->unsigned()->default(0);
				$table->timestamp('datetimesubmitted');
				$table->timestamp('datetimestarted');
				$table->timestamp('datetimecompleted');
				$table->integer('pid')->unsigned()->default(0);
				$table->integer('returnstatus')->unsigned()->default(0);
				$table->string('output', 150);
				$table->index(['targetobject', 'userid']);
				$table->index('datetimecompleted');
			});
		}

		if (!Schema::hasTable('messagequeuetypes'))
		{
			Schema::create('messagequeuetypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 24);
				$table->integer('resourceid')->unsigned()->default(0);
				$table->string('classname', 24);
			});
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'messagequeue',
			'messagequeuetypes'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
