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
		if (!Schema::hasTable('cron_jobs'))
		{
			Schema::create('cron_jobs', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 255);
				$table->integer('state')->default(0);
				$table->string('plugin', 255);
				$table->string('event', 255);
				$table->dateTime('last_run')->nullable();
				$table->dateTime('next_run')->nullable();
				$table->string('recurrence', 255);
				$table->dateTime('created_at')->nullable();
				$table->integer('created_by')->unsigned()->default(0);
				$table->dateTime('updated_at')->nullable();
				$table->integer('updated_by')->unsigned()->default(0);
				$table->integer('active')->unsigned()->default(0);
				$table->integer('ordering')->unsigned()->default(0);
				$table->string('params', 5000)->nullable();
				$table->dateTime('publish_up')->nullable();
				$table->dateTime('publish_down')->nullable();
				$table->index('state');
				$table->index('created_by');
			});
			//$this->info('Created `cron_jobs` table.');
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		Schema::dropIfExists('cron_jobs');

		//$this->info('Dropped `cron_jobs` table.');
	}
}
