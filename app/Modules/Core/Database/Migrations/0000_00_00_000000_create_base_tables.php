<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing base tables
 **/
class CreateBaseTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('extensions'))
		{
			Schema::create('extensions', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 100);
				$table->string('type', 20);
				$table->string('element', 100);
				$table->string('folder', 100)->nullable();
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->tinyInteger('enabled')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->tinyInteger('protected')->unsigned()->default(0);
				$table->text('params')->nullable();
				$table->integer('checked_out')->unsigned()->default(0);
				$table->dateTime('checked_out_time')->nullable();
				$table->integer('ordering')->unsigned()->default(0);
				$table->integer('state')->unsigned()->default(0);
				$table->dateTime('updated_at')->nullable();
				$table->integer('updated_by')->unsigned()->default(0);
				$table->index(['element', 'client_id']);
				$table->index(['element', 'folder', 'client_id']);
				$table->index(['type', 'element', 'folder', 'client_id']);
			});

			/*DB::table('extensions')->insert([
				'name' => 'core',
				'type' => 'module',
				'element' => 'core',
				'client_id' => 0,
				'enabled' => 1,
				'access' => 1,
				'protected' => 1,
			]);*/
		}

		if (!Schema::hasTable('collegedept'))
		{
			Schema::create('collegedept', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parentid')->unsigned()->default(0);
				$table->string('name', 255);
				$table->index('parentid');
			});
		}

		if (!Schema::hasTable('fieldofscience'))
		{
			Schema::create('fieldofscience', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parentid')->unsigned()->default(0);
				$table->string('name', 255);
				$table->index('parentid');
			});
		}

		if (!Schema::hasTable('timeperiods'))
		{
			Schema::create('timeperiods', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 32);
				$table->string('singular', 32);
				$table->string('plural', 32);
				$table->integer('unixtime')->unsigned()->default(0);
				$table->tinyInteger('months')->unsigned()->default(0);
				$table->tinyInteger('warningtimeperiodid')->unsigned()->default(0);
			});

			$timeperiods = array(
				array(
					'name' => 'daily',
					'singular' => 'day',
					'plural' => 'days',
					'unixtime' => 86400,
					'months' => 0,
					'warningtimeperiodid' => 5,
				),
				array(
					'name' => 'weekly',
					'singular' => 'week',
					'plural' => 'weeks',
					'unixtime' => 604800,
					'months' => 0,
					'warningtimeperiodid' => 1,
				),
				array(
					'name' => 'monthly',
					'singular' => 'month',
					'plural' => 'months',
					'unixtime' => 0,
					'months' => 1,
					'warningtimeperiodid' => 2,
				),
				array(
					'name' => 'annual',
					'singular' => 'year',
					'plural' => 'years',
					'unixtime' => 0,
					'months' => 12,
					'warningtimeperiodid' => 3,
				),
				array(
					'name' => 'hourly',
					'singular' => 'hour',
					'plural' => 'hours',
					'unixtime' => 3600,
					'months' => 0,
					'warningtimeperiodid' => 0,
				),
			);

			foreach ($timeperiods as $timeperiod)
			{
				DB::table('timeperiods')->insert($timeperiod);
			}
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'extensions',
			'collegedept',
			'fieldofscience',
			'timeperiods',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
