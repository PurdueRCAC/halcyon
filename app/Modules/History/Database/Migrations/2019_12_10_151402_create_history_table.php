<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return null
	 */
	public function up()
	{
		if (!Schema::hasTable('history'))
		{
			Schema::create('history', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('user_id')->unsigned()->nullable();
				$table->integer('historable_id')->unsigned();
				$table->string('historable_type');
				$table->string('historable_table');
				$table->string('action');
				$table->json('old');
				$table->json('new');
				$table->timestamps();
				$table->index(['historable_type', 'historable_id']);
				$table->index('action');
			});
		}

		if (!Schema::hasTable('log'))
		{
			Schema::create('log', function (Blueprint $table)
			{
				$table->increments('id');
				$table->dateTime('datetime')->nullable();
				$table->string('ip', 39);
				$table->string('hostname', 128);
				$table->integer('userid')->unsigned()->default(0);
				$table->smallInteger('status')->unsigned()->default(0);
				$table->string('transportmethod', 6);
				$table->string('servername', 128);
				$table->string('uri', 128);
				$table->string('app', 20);
				$table->string('classname', 32);
				$table->string('classmethod', 16);
				$table->string('objectid', 32);
				$table->string('payload', 2000);
				$table->integer('groupid')->unsigned()->default(0);
				$table->integer('targetuserid')->unsigned()->default(0);
				$table->integer('targetobjectid')->unsigned()->default(0);
				$table->index(['app', 'classname', 'objectid', 'datetime'], 'app_3');
				$table->index('groupid');
				$table->index(['app', 'classname', 'groupid', 'datetime'], 'app');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return null
	 */
	public function down()
	{
		Schema::dropIfExists('history');
		Schema::dropIfExists('log');
	}
}
