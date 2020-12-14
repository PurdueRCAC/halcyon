<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing news tables
 **/
class CreateNewsTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('news'))
		{
			Schema::create('news', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('edituserid')->unsigned()->default(0);
				$table->integer('newstypeid')->unsigned()->default(0);
				$table->typeInteger('published')->unsigned()->default(0);
				$table->typeInteger('template')->unsigned()->default(0);
				$table->string('headline', 255);
				$table->text('body');
				$table->string('location', 32)->nullable();
				$table->timestamp('datetimenews');
				$table->timestamp('datetimenewsend');
				$table->timestamp('datetimeupdate');
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeedited');
				$table->timestamp('datetimemailed');
				$table->integer('lastmailuserid')->unsigned()->default(0);
				$table->index(['newstypeid', 'datetimenews']);
				$table->index('userid');
				$table->index('edituserid');
				$table->index('lastmailuserid');
			});
		}

		if (!Schema::hasTable('newsstemmedtext'))
		{
			Schema::create('newsstemmedtext', function (Blueprint $table)
			{
				$table->increments('id');
				$table->text('stemmedtext');
			});

			DB::statement('ALTER TABLE newsstemmedtext ADD FULLTEXT search(stemmedtext)');
		}

		if (!Schema::hasTable('newstypes'))
		{
			Schema::create('newstypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 32);
				$table->typeInteger('tagresources')->unsigned()->default(0);
				$table->typeInteger('location')->unsigned()->default(0);
				$table->typeInteger('future')->unsigned()->default(0);
				$table->typeInteger('ongoing')->unsigned()->default(0);
				$table->typeInteger('tagusers')->unsigned()->default(0);
				$table->typeInteger('calendar')->unsigned()->default(0);
			});
		}

		if (!Schema::hasTable('newsupdates'))
		{
			Schema::create('newsupdates', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('edituserid')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeedited');
				$table->timestamp('datetimeremoved');
				$table->text('body');
				$table->integer('newsid')->unsigned()->default(0);
				$table->index('newsid');

				$table->foreign('newsid')->references('id')->on('news');
			});
		}

		if (!Schema::hasTable('newsresources'))
		{
			Schema::create('newsresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('newsid')->unsigned()->default(0);
				$table->integer('resourceid')->unsigned()->default(0);
				$table->index('resourceid');
				$table->index('newsid');

				$table->foreign('newsid')->references('id')->on('news');
				$table->foreign('resourceid')->references('id')->on('resources');
			});
		}

		if (!Schema::hasTable('newsassociations'))
		{
			Schema::create('newsassociations', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('newsid')->unsigned()->default(0);
				$table->integer('associd')->unsigned()->default(0);
				$table->string('assoctype');
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->index(['assoctype', 'associd']);
				$table->index('newsid');

				$table->foreign('newsid')->references('id')->on('news');
			});
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'news',
			'newsstemmedtext',
			'newstypes',
			'newsupdates',
			'newsresources',
			'newsassociations',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
