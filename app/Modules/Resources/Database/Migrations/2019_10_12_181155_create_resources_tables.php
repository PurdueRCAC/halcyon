<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('resources'))
		{
			Schema::create('resources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 32);
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->smallInteger('parentid')->unsigned()->default(0);
				$table->tinyInteger('batchsystem')->default(0);
				$table->char('rolename', 32);
				$table->char('listname', 32);
				$table->tinyInteger('display')->unsigned()->default(0);
				$table->tinyInteger('resourcetype')->unsigned()->default(0);
				$table->tinyInteger('producttype')->unsigned()->default(0);
				$table->index(['datetimeremoved', 'datetimecreated']);
			});
			$this->info('Created `resources` table.');
		}

		if (!Schema::hasTable('resourcesubresources'))
		{
			Schema::create('resourcesubresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0);
				$table->smallInteger('subresourceid')->unsigned()->default(0);
				$table->index('resourceid');
				$table->index('subresourceid');
				$table->index(['resourceid', 'subresourceid']);
			});
			$this->info('Created `resourcesubresources` table.');
		}

		if (!Schema::hasTable('resourcetypes'))
		{
			Schema::create('resourcetypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 20);
			});
			$this->info('Created `resourcetypes` table.');
		}

		if (!Schema::hasTable('batchsystems'))
		{
			Schema::create('batchsystems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 16);
			});
			$this->info('Created `batchsystems` table.');
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$tables = array(
			'resources',
			'resourcesubresources',
			'resourcetypes',
			'batchsystems',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
			$this->info('Dropped `' . $table . '` table.');
		}
	}
}
