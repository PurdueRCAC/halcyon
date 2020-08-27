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
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimecontact');
				$table->integer('notice')->unsigned()->default(0);
				$table->timestamp('datetimegroupid');
				$table->index(['groupid', 'userid', 'datetimecontact']);
				$table->index(['userid', 'datetimecontact']);
				$table->index(['groupid', 'datetimecontact']);
			});
			//$this->info('Created `contactreports` table.');
		}

		if (!Schema::hasTable('contactreportstems'))
		{
			Schema::create('contactreportstems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->text('stemmedtext');
			});
			//$this->info('Created `contactreportstems` table.');
		}

		if (!Schema::hasTable('contactreportusers'))
		{
			Schema::create('contactreportusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->index(['contactreportid', 'userid']);
				$table->index('userid');
			});
			//$this->info('Created `contactreportusers` table.');
		}

		if (!Schema::hasTable('contactreportcomments'))
		{
			Schema::create('contactreportcomments', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('contactreportid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->text('comment');
				$table->text('stemmedcomment');
				$table->integer('notice')->unsigned()->default(0);
				$table->index('contactreportid');
			});
			//$this->info('Created `contactreportcomments` table.');
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
			//$this->info('Created `contactreportresources` table.');
		}

		if (!Schema::hasTable('linkusers'))
		{
			Schema::create('linkusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('targetuserid')->unsigned()->default(0);
				$table->integer('membertype')->unsigned()->default(0);
				$table->timestamp('datecreated');
				$table->timestamp('dateremoved');
				$table->timestamp('datelastseen');
				$table->index(['userid', 'membertype', 'dateremoved', 'datecreated']);
				$table->index(['targetuserid', 'membertype', 'dateremoved', 'datecreated']);
			});
			//$this->info('Created `linkusers` table.');
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
			'linkusers',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);

			//$this->info('Dropped `' . $table . '` table.');
		}
	}
}
