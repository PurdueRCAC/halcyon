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
	public function up(): void
	{
		if (!Schema::hasTable('contactreports'))
		{
			Schema::create('contactreports', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->string('report', 8096);
				$table->string('stemmedreport', 8096);
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimecontact')->nullable();
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->dateTime('datetimegroupid')->nullable();
				$table->integer('contactreporttypeid')->unsigned()->default(0)->comment('FK to contactreporttypes.id');
				$table->index(['groupid', 'userid', 'datetimecontact'], 'groupid');
				$table->index(['userid', 'datetimecontact'], 'userid');
				$table->index(['groupid', 'datetimecontact'], 'datetimecontact');
				$table->index('contactreporttypeid');
				$table->index('notice');
			});

			DB::statement('ALTER TABLE contactreports ADD FULLTEXT (stemmedreport)');
		}

		if (!Schema::hasTable('contactreportusers'))
		{
			Schema::create('contactreportusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0)->comment('FK to contactreports.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimelastnotify')->nullable();
				$table->index(['contactreportid', 'userid'], 'contactreportid');
				$table->index('userid');
			});
		}

		if (!Schema::hasTable('contactreportcomments'))
		{
			Schema::create('contactreportcomments', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0)->comment('FK to contactreports.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->string('comment', 8096);
				$table->string('stemmedcomment', 8096);
				$table->dateTime('datetimecreated')->nullable();
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index('contactreportid');
				$table->index('userid');
				$table->index('notice');
			});

			DB::statement('ALTER TABLE contactreportcomments ADD FULLTEXT (stemmedcomment)');
		}

		if (!Schema::hasTable('contactreportresources'))
		{
			Schema::create('contactreportresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0)->comment('FK to contactreports.id');
				$table->integer('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
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
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('targetuserid')->unsigned()->default(0)->comment('FK to users.id');
				$table->tinyInteger('membertype')->unsigned()->default(0)->comment('FK to membertypes.id');
				$table->dateTime('datecreated')->nullable();
				$table->dateTime('dateremoved')->nullable();
				$table->dateTime('datelastseen')->nullable();
				$table->index(['userid', 'membertype', 'dateremoved', 'datecreated'], 'userid');
				$table->index(['targetuserid', 'membertype', 'dateremoved', 'datecreated'], 'targetuserid');
			});
		}
	}

	/**
	 * Down
	 **/
	public function down(): void
	{
		$tables = array(
			'contactreports',
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
