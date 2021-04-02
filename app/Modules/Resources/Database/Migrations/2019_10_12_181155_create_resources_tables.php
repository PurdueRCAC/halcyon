<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->smallInteger('parentid')->unsigned()->default(0)->comment('Parent resource. FK to resources.id');
				$table->tinyInteger('batchsystem')->default(0)->comment('FK to batchsystems.id');
				$table->char('rolename', 32);
				$table->char('listname', 32);
				$table->tinyInteger('display')->unsigned()->default(0);
				$table->tinyInteger('resourcetype')->unsigned()->default(0);
				$table->tinyInteger('producttype')->unsigned()->default(0);
				$table->index(['datetimeremoved', 'datetimecreated'], 'datetimeremoved');
				$table->index('parentid');
				$table->index('batchsystem');
			});
		}

		if (!Schema::hasTable('subresources'))
		{
			Schema::create('subresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 32);
				$table->char('cluster', 12);
				$table->tinyInteger('nodecores')->unsigned()->default(0);
				$table->char('nodemem', 5);
				$table->tinyInteger('nodegpus')->unsigned()->default(0);
				$table->char('nodeattributes', 16);
				$table->char('description', 255);
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->tinyInteger('notice')->unsigned()->default(0);
			});
		}

		if (!Schema::hasTable('resourcesubresources'))
		{
			Schema::create('resourcesubresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->smallInteger('subresourceid')->unsigned()->default(0)->comment('FK to subresources.id');
				$table->index('resourceid');
				$table->index('subresourceid');
				$table->index(['resourceid', 'subresourceid'], 'resourceid_2');
			});
		}

		if (!Schema::hasTable('resourcetypes'))
		{
			Schema::create('resourcetypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 20);
			});

			DB::table('resourcetypes')->insert([
				'name' => 'Compute'
			]);

			DB::table('resourcetypes')->insert([
				'name' => 'Storage'
			]);
		}

		if (!Schema::hasTable('batchsystems'))
		{
			Schema::create('batchsystems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 16);
			});

			$batchsystems = array(
				'PBS',
				'Condor',
				'SLURM',
				'Nimbus',
				'WinHPC',
				'Hadoop'
			);

			foreach ($batchsystems as $batchsystem)
			{
				DB::table('batchsystems')->insert([
					'name' => $batchsystem
				]);
			}
		}

		if (!Schema::hasTable('jobscripts'))
		{
			Schema::create('jobscripts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('jobid')->unsigned()->default(0);
				$table->string('system', 64);
				$table->text('script');
				$table->text('env');
				$table->dateTime('datetimecreated')->nullable();
				$table->unique(['jobid', 'system']);
				$table->index('system');
				$table->index('datetimecreated');
			});
		}

		if (!Schema::hasTable('box'))
		{
			Schema::create('box', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('datetimecreated')->nullable();
			});
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
			'subresources',
			'batchsystems',
			'jobscripts',
			'box'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
