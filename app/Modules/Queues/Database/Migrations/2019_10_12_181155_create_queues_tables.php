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
				$table->integer('schedulerid')->unsigned()->default(0)->comment('FK to schedulers.id');
				$table->integer('subresourceid')->unsigned()->default(0)->comment('FK to subresources.id');
				$table->char('name', 64);
				$table->integer('groupid')->default(-1)->comment('FK to groups.id');
				$table->tinyInteger('queuetype')->unsigned()->default(0);
				$table->tinyInteger('automatic')->unsigned()->default(0);
				$table->tinyInteger('free')->unsigned()->default(0);
				$table->integer('schedulerpolicyid')->unsigned()->default(0)->comment('FK to schedulerpolicies.id');
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
				$table->tinyInteger('aclusersenabled')->unsigned()->default(1);
				$table->char('aclgroups', 255);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->dateTime('datetimelastseen');
				$table->smallInteger('maxjobfactor')->unsigned()->default(2);
				$table->smallInteger('maxjobuserfactor')->unsigned()->default(1);
				$table->index('groupid');
				$table->index('subresourceid');
				$table->index('schedulerpolicyid');
				$table->index('schedulerid');
			});
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
				$table->integer('queueid')->unsigned()->default(0)->comment('Queue being sold to. FK to queues.id');
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->smallInteger('nodecount')->unsigned()->default(0);
				$table->smallInteger('corecount')->unsigned()->default(0);
				$table->integer('sellerqueueid')->unsigned()->default(0)->comment('Queue making the sell. FK to queues.id');
				$table->string('comment', 2000);
				$table->index(['queueid', 'datetimestart', 'datetimestop']);
				$table->index('sellerqueueid');
			});
		}

		if (!Schema::hasTable('queueloans'))
		{
			Schema::create('queueloans', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0)->comment('Queue being loaded to. FK to queues.id');
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->smallInteger('nodecount')->unsigned()->default(0);
				$table->smallInteger('corecount')->unsigned()->default(0);
				$table->integer('lenderqueueid')->unsigned()->default(0)->comment('Queue being loaned from. FK to queues.id');
				$table->string('comment', 2000);
				$table->index(['queueid', 'datetimestart', 'datetimestop'], 'queueid');
				$table->index('lenderqueueid');
			});
		}

		if (!Schema::hasTable('queueusers'))
		{
			Schema::create('queueusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0)->comment('FK to queues.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('userrequestid')->unsigned()->default(0)->comment('FK to userrequests.id');
				$table->tinyInteger('membertype')->unsigned()->default(0)->comment('FK to membertypes.id');
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->dateTime('datetimelastseen');
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index(['queueid', 'userid', 'membertype', 'datetimeremoved'], 'queueid');
				$table->index('userrequestid');
				$table->index(['notice', 'queueid'], 'notice');
			});
		}

		if (!Schema::hasTable('queuewalltimes'))
		{
			Schema::create('queuewalltimes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('queueid')->unsigned()->default(0)->comment('FK to queues.id');
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->integer('walltime')->unsigned()->default(0);
				$table->index(['queueid', 'datetimestart', 'datetimestop', 'queueid']);
			});
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
				$table->integer('queuesubresourceid')->unsigned()->default(0)->comment('FK to subresources.id');
				$table->string('hostname', 64);
				$table->tinyInteger('batchsystem')->unsigned()->default(0);
				$table->tinyInteger('schedulerpolicyid')->unsigned()->default(1)->comment('FK to schedulerpolicies.id');
				$table->integer('defaultmaxwalltime')->unsigned()->default(0);
				$table->string('teragridresource', 64);
				$table->tinyInteger('teragridaggregate')->unsigned()->default(1);
				$table->dateTime('datetimedraindown');
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->dateTime('datetimelastimportstart');
				$table->dateTime('datetimelastimportstop');
				$table->index(['queuesubresourceid', 'datetimecreated', 'datetimeremoved'], 'queuesubresourceid');
				$table->index('batchsystem');
				$table->index('schedulerpolicyid');
			});
		}

		if (!Schema::hasTable('schedulerreservations'))
		{
			Schema::create('schedulerreservations', function (Blueprint $table)
			{
				$table->increments('id');
				$table->tinyInteger('schedulerid')->unsigned()->default(0)->comment('FK to schedulers.id');
				$table->string('name', 32);
				$table->string('nodes', 255);
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->index(['schedulerid', 'datetimestop'], 'schedulerid');
				$table->index(['schedulerid', 'datetimestart', 'datetimestop'], 'schedulerid_2');
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
		}
	}
}
