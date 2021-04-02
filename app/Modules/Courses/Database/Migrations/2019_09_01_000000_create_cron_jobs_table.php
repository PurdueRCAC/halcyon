<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration script for installing cron tables
 **/
class CreateCronJobsTable extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('classaccounts'))
		{
			Schema::create('classaccounts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('crn', 8);
				$table->string('department', 4);
				$table->string('coursenumber', 8);
				$table->string('classname', 255);
				$table->integer('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->integer('notice')->unsigned()->default(0);
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->string('semester', 16);
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->integer('studentcount')->unsigned()->default(0);
				$table->string('reference', 64);
				$table->index('resourceid');
				$table->index('userid');
			});
		}

		if (!Schema::hasTable('classusers'))
		{
			Schema::create('classusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('classaccountid')->unsigned()->default(0)->comment('FK to classaccounts.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('membertype')->unsigned()->default(0)->comment('FK to membertypes.id');
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->integer('notice')->unsigned()->default(0);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->index('classaccountid');
				$table->index(['userid', 'membertype', 'datetimecreated', 'datetimeremoved'], 'userid');
			});
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		Schema::dropIfExists('classaccounts');
		Schema::dropIfExists('classusers');
	}
}
