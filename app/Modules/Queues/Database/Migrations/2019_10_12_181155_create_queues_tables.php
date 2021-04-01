<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateQueuesTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('queues'))
		{
			Schema::create('queues', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('schedulerid')->unsigned()->default(0);
				$table->integer('subresourceid')->unsigned()->default(0);
				$table->char('name', 64);
				$table->integer('groupid')->unsigned()->default(0);
				$table->tinyInteger('queuetype')->unsigned()->default(0);
				$table->tinyInteger('automatic')->unsigned()->default(0);
				$table->tinyInteger('free')->unsigned()->default(0);
				$table->integer('schedulerpolicyid')->unsigned()->default(0);
				$table->tinyInteger('enabled')->unsigned()->default(0);
				$table->tinyInteger('started')->unsigned()->default(0);
				$table->tinyInteger('reservation')->unsigned()->default(0);
				$table->char('cluster', 32);
				$table->smallInteger('priority')->unsigned()->default(0);
				$table->integer('defaultwalltime')->unsigned()->default(0);
				$table->smallInteger('maxjobsqueued')->unsigned()->default(0);
				$table->smallInteger('maxjobsqueueduser')->unsigned()->default(0);
				$table->smallInteger('maxjobsrun')->unsigned()->default(0);
				$table->smallInteger('maxjobsrunuser')->unsigned()->default(0);
				$table->smallInteger('maxjobcores')->unsigned()->default(0);
				$table->smallInteger('nodecoresdefault')->unsigned()->default(0);
				$table->smallInteger('nodecoresmin')->unsigned()->default(0);
				$table->smallInteger('nodecoresmax')->unsigned()->default(0);
				$table->char('nodememmin', 5);
				$table->char('nodememmax', 5);
				$table->tinyInteger('aclusersenabled')->unsigned()->default(0);
				$table->char('aclgroups', 255);
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->timestamp('datetimelastseen');
				$table->smallInteger('maxjobfactor')->unsigned()->default(0);
				$table->smallInteger('maxjobuserfactor')->unsigned()->default(0);
				$table->index('groupid');
				$table->index('subresourceid');
				$table->index('schedulerpolicyid');
				$table->index('schedulerid');
			});
			//$this->info('Created `queues` table.');
		}

		if (!Schema::hasTable('queuetypes'))
		{
			Schema::create('queuetypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 20);
			});

			$types = array(
				'compute queue',
				'storage',
				'virtual machine',
				'hadoop'
			);

			foreach ($types as $type)
			{
				DB::table('queuetypes')->insert([
					'name' => $type
				]);
			}
		}

		if (!Schema::hasTable('queuesizes'))
		{
			Schema::create('queuesizes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0);
				$table->timestamp('datetimestart');
				$table->timestamp('datetimestop');
				$table->smallInteger('nodecount')->unsigned()->default(0);
				$table->smallInteger('corecount')->unsigned()->default(0);
				$table->integer('sellerqueueid')->unsigned()->default(0);
				$table->char('comment', 2000);
				$table->index(['queueid', 'datetimestart', 'datetimestop']);
				$table->index('sellerqueueid');
			});
			//$this->info('Created `queuesizes` table.');
		}

		if (!Schema::hasTable('queueloans'))
		{
			Schema::create('queueloans', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0);
				$table->timestamp('datetimestart');
				$table->timestamp('datetimestop');
				$table->smallInteger('nodecount')->unsigned()->default(0);
				$table->smallInteger('corecount')->unsigned()->default(0);
				$table->integer('lenderqueueid')->unsigned()->default(0);
				$table->char('comment', 2000);
				$table->index(['queueid', 'datetimestart', 'datetimestop']);
				$table->index('lenderqueueid');
			});
			//$this->info('Created `queueloans` table.');
		}

		if (!Schema::hasTable('queueusers'))
		{
			Schema::create('queueusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('userrequestid')->unsigned()->default(0);
				$table->tinyInteger('membertype')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->timestamp('datetimelastseen');
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index(['queueid', 'userid', 'membertype', 'datetimeremoved']);
				$table->index('userrequestid');
				$table->index('notice');
			});
			//$this->info('Created `queueusers` table.');
		}

		if (!Schema::hasTable('queuewalltimes'))
		{
			Schema::create('queuewalltimes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0);
				$table->timestamp('datetimestart');
				$table->timestamp('datetimestop');
				$table->integer('walltime')->unsigned()->default(0);
				$table->index(['queueid', 'datetimestart', 'datetimestop']);
			});
			//$this->info('Created `queuewalltimes` table.');
		}

		if (!Schema::hasTable('schedulerpolicies'))
		{
			Schema::create('schedulerpolicies', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('code', 16);
				$table->string('name', 64);
			});

			$schedulerpolicies = array(
				array(
					'name' => 'World Shared Node Scheduling',
					'code' => 'SHARED',
				),
				array(
					'name' => 'Whole Node Scheduling',
					'code' => 'SINGLEJOB',
				),
				array(
					'name' => 'Single User Shared Scheduling',
					'code' => 'SINGLEUSER',
				),
				array(
					'name' => 'Single Project Shared Scheduling',
					'code' => 'SINGLEACCOUNT',
				),
				array(
					'name' => 'Single Group Shared Scheduling',
					'code' => 'SINGLEGROUP',
				),
			);

			foreach ($schedulerpolicies as $policy)
			{
				DB::table('schedulerpolicies')->insert($policy);
			}
		}

		if (!Schema::hasTable('schedulers'))
		{
			Schema::create('schedulers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queuesubresourceid')->unsigned()->default(0);
				$table->string('hostname', 64);
				$table->tinyInteger('batchsystem')->unsigned()->default(0);
				$table->tinyInteger('schedulerpolicyid')->unsigned()->default(0);
				$table->integer('defaultmaxwalltime')->unsigned()->default(0);
				$table->string('teragridresource', 64);
				$table->tinyInteger('teragridaggregate')->unsigned()->default(0);
				$table->timestamp('datetimedraindown');
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->timestamp('datetimelastimportstart');
				$table->timestamp('datetimelastimportstop');
				$table->index(['queuesubresourceid', 'datetimecreated', 'datetimeremoved']);
				$table->index('batchsystem');
				$table->index('schedulerpolicyid');
			});
			//$this->info('Created `schedulers` table.');
		}

		if (!Schema::hasTable('schedulerreservations'))
		{
			Schema::create('schedulerreservations', function (Blueprint $table)
			{
				$table->increments('id');
				$table->tinyInteger('schedulerid')->unsigned()->default(0);
				$table->string('name', 32);
				$table->string('nodes', 255);
				$table->timestamp('datetimestart');
				$table->timestamp('datetimestop');
				$table->index(['schedulerid', 'datetimestop']);
				$table->index(['schedulerid', 'datetimestart', 'datetimestop']);
			});
			//$this->info('Created `schedulerreservations` table.');
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$tables = array(
			'queues',
			'queuetypes',
			'queuesizes',
			'queueloans',
			'queueusers',
			'queuewalltimes',
			'schedulerpolicies',
			'schedulers',
			'schedulerreservations',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
			//$this->info('Deleted `' . $table . '` table.');
		}
	}
}
