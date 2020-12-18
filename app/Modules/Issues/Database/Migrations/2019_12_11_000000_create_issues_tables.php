<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration script for installing news tables
 **/
class CreateIssuesTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('issues'))
		{
			Schema::create('issues', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->text('report');
				$table->text('stemmedreport');
				$table->timestamp('datetimecreated');
				$table->integer('issuetodoid')->unsigned()->default(0);
				$table->index(['userid', 'datetimecreated']);
				$table->index('stemmedreport');
			});

			DB::statement('ALTER TABLE issues ADD FULLTEXT search(stemmedtext)');
			//$this->info('Created `issues` table.');
		}

		if (!Schema::hasTable('issuecomments'))
		{
			Schema::create('issuecomments', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('issueid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->text('comment');
				$table->text('stemmedcomment');
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->tinyInteger('resolution')->unsigned()->default(0);
				$table->index('issueid');
				$table->index('userid');
				$table->index('resolution');
			});

			DB::statement('ALTER TABLE issuecomments ADD FULLTEXT search(stemmedcomment)');
			//$this->info('Created `issuecomments` table.');
		}

		if (!Schema::hasTable('issueresources'))
		{
			Schema::create('issueresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('issueid')->unsigned()->default(0);
				$table->integer('resourceid')->unsigned()->default(0);
				$table->index('resourceid');
				$table->index('issueid');
			});
			//$this->info('Created `issueresources` table.');
		}

		if (!Schema::hasTable('issuetodos'))
		{
			Schema::create('issuetodos', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->string('name');
				$table->text('description');
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->integer('recurringtimeperiodid')->unsigned()->default(0);
				$table->index(['userid', 'datetimecreated']);
			});
			//$this->info('Created `issuetodos` table.');
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'issues',
			'issuecomments',
			'issueresources',
			'issuetodos'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);

			//$this->info('Dropped `' . $table . '` table.');
		}
	}
}
