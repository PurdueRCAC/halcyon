<?php

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
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('edituserid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('newstypeid')->unsigned()->default(0)->comment('FK to newstypes.id');
				$table->tinyInteger('published')->unsigned()->default(0);
				$table->tinyInteger('template')->unsigned()->default(0);
				$table->string('headline', 255);
				$table->string('body', 15000);
				$table->string('location', 32)->nullable();
				$table->dateTime('datetimenews')->nullable();
				$table->dateTime('datetimenewsend')->nullable();
				$table->dateTime('datetimeupdate')->nullable();
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeedited')->nullable();
				$table->dateTime('datetimemailed')->nullable();
				$table->integer('lastmailuserid')->unsigned()->default(0)->comment('FK to users.id');
				$table->string('url', 255)->nullable();
				$table->index(['newstypeid', 'datetimenews'], 'newstypeid');
				$table->index('userid');
				$table->index('edituserid');
				$table->index('lastmailuserid');
			});
		}

		if (!Schema::hasTable('newsstemmedtext'))
		{
			Schema::create('newsstemmedtext', function (Blueprint $table)
			{
				$table->increments('id')->comment('This should be the same as news.id');
				$table->string('stemmedtext', 16200);
			});

			DB::statement('ALTER TABLE newsstemmedtext ADD FULLTEXT (stemmedtext)');
		}

		if (!Schema::hasTable('newstypes'))
		{
			Schema::create('newstypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 32);
				$table->tinyInteger('tagresources')->unsigned()->default(0);
				$table->tinyInteger('location')->unsigned()->default(0);
				$table->tinyInteger('future')->unsigned()->default(0);
				$table->tinyInteger('ongoing')->unsigned()->default(0);
				$table->tinyInteger('tagusers')->unsigned()->default(0);
				$table->tinyInteger('calendar')->unsigned()->default(0);
				$table->tinyInteger('url')->unsigned()->default(0);
			});

			$types = array(
				array(
					'name' => 'Outages and Maintenance',
					'tagresources' => 1,
					'location' => 0,
					'future' => 1,
					'ongoing' => 1,
					'tagusers' => 0,
					'calendar' => 1,
					'url' => 0,
				),
				array(
					'name' => 'Announcements',
					'tagresources' => 1,
					'location' => 0,
					'future' => 1,
					'ongoing' => 0,
					'tagusers' => 0,
					'calendar' => 0,
					'url' => 0,
				),
				array(
					'name' => 'Science Highlights',
					'tagresources' => 0,
					'location' => 0,
					'future' => 0,
					'ongoing' => 0,
					'tagusers' => 0,
					'calendar' => 0,
					'url' => 0,
				),
				array(
					'name' => 'Events',
					'tagresources' => 0,
					'location' => 1,
					'future' => 1,
					'ongoing' => 0,
					'tagusers' => 1,
					'calendar' => 1,
					'url' => 1,
				),
				array(
					'name' => 'Office Hours',
					'tagresources' => 0,
					'location' => 1,
					'future' => 1,
					'ongoing' => 0,
					'tagusers' => 1,
					'calendar' => 1,
					'url' => 1,
				),
			);

			foreach ($types as $type)
			{
				DB::table('newstypes')->insert($type);
			}
		}

		if (!Schema::hasTable('newsupdates'))
		{
			Schema::create('newsupdates', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('edituserid')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeedited')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->string('body', 15000);
				$table->integer('newsid')->unsigned()->default(0)->comment('FK to news.id');
				$table->index(['newsid', 'datetimecreated', 'datetimeremoved'], 'newsid');

				$table->foreign('newsid')->references('id')->on('news');
			});
		}

		if (!Schema::hasTable('newsresources'))
		{
			Schema::create('newsresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('newsid')->unsigned()->default(0)->comment('FK to news.id');
				$table->integer('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
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
				$table->integer('newsid')->unsigned()->default(0)->comment('FK to news.id');
				$table->integer('associd')->unsigned()->default(0);
				$table->string('assoctype');
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->index(['assoctype', 'associd'], 'assoc');
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
