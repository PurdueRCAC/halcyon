<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration script for installing feedback table
 **/
class CreateFeedbackTable extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('kb_feedback'))
		{
			Schema::create('kb_feedback', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('target_id')->unsigned()->default(0);
				$table->string('ip', 15);
				$table->string('type', 10);
				$table->integer('user_id')->unsigned()->default(0);
				$table->timestamp('created_at')->nullable();
				$table->timestamp('updated_at')->nullable();
				$table->string('comments', 255);
				$table->index(['target_id', 'type']);
				$table->index('user_id');
			});
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'kb_feedback',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
