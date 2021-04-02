<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing news tables
 **/
class CreateContactReportsTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('contactreports'))
		{
			Schema::create('contactreports', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->text('report');
				$table->text('stemmedreport');
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimecontact');
				$table->integer('notice')->unsigned()->default(0);
				$table->dateTime('datetimegroupid');
				$table->integer('contactreporttypeid')->unsigned()->default(0);
				$table->index(['groupid', 'userid', 'datetimecontact'], 'groupid');
				$table->index(['userid', 'datetimecontact'], 'userid');
				$table->index(['groupid', 'datetimecontact'], 'datetimecontact');
				$table->index('contactreporttypeid');
			});

			DB::statement('ALTER TABLE contactreports ADD FULLTEXT search(stemmedreport)');
		}

		if (!Schema::hasTable('contactreportstems'))
		{
			Schema::create('contactreportstems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->text('stemmedtext');
			});

			DB::statement('ALTER TABLE contactreports ADD FULLTEXT search(stemmedtext)');
		}

		if (!Schema::hasTable('contactreportusers'))
		{
			Schema::create('contactreportusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimelastnotify');
				$table->index(['contactreportid', 'userid'], 'contactreportid');
				$table->index('userid');
			});
		}

		if (!Schema::hasTable('contactreportcomments'))
		{
			Schema::create('contactreportcomments', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->text('comment');
				$table->text('stemmedcomment');
				$table->dateTime('datetimecreated');
				$table->integer('notice')->unsigned()->default(0);
				$table->index('contactreportid');
			});

			DB::statement('ALTER TABLE contactreportcomments ADD FULLTEXT search(stemmedcomment)');
		}

		if (!Schema::hasTable('contactreportresources'))
		{
			Schema::create('contactreportresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0);
				$table->integer('resourceid')->unsigned()->default(0);
				$table->index('resourceid');
				$table->index('contactreportid');
			});
		}

		if (!Schema::hasTable('contactreporttypes'))
		{
			Schema::create('contactreporttypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 32);
				$table->tinyInteger('timeperiodid')->unsigned()->default(0);
				$table->tinyInteger('timeperiodcount')->unsigned()->default(0);
				$table->tinyInteger('timeperiodlimit')->unsigned()->default(0);
			});

			$types = array(
				'Office Hours',
				'Email / Phone',
				'Personal Meeting',
				'New Faculty Meeting',
				'Strategic Partner Meeting',
				'Class Account Request',
				'Workshop Account Request',
			);

			foreach ($types as $type)
			{
				DB::table('contactreporttypes')->insert([
					'name' => $type
				]);
			}
		}

		if (!Schema::hasTable('linkusers'))
		{
			Schema::create('linkusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('targetuserid')->unsigned()->default(0);
				$table->integer('membertype')->unsigned()->default(0);
				$table->dateTime('datecreated');
				$table->dateTime('dateremoved');
				$table->dateTime('datelastseen');
				$table->index(['userid', 'membertype', 'dateremoved', 'datecreated'], 'userid');
				$table->index(['targetuserid', 'membertype', 'dateremoved', 'datecreated'], 'targetuserid');
			});
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'contactreports',
			'contactreportstems',
			'contactreportusers',
			'contactreportcomments',
			'contactreportresources',
			'contactreporttypes',
			'linkusers',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
