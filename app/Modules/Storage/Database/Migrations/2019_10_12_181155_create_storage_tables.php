<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateStorageTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('storagedirs'))
		{
			Schema::create('storagedirs', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('parentstoragedirid')->unsigned()->default(0)->comment('Parent storagedirs.id');
				$table->char('name', 32);
				$table->char('path', 255);
				$table->bigInteger('bytes')->default(0);
				$table->integer('owneruserid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('unixgroupid')->unsigned()->default(0)->comment('FK to unixgroups.id');
				$table->tinyInteger('ownerread')->default(0);
				$table->tinyInteger('ownerwrite')->default(0);
				$table->tinyInteger('groupread')->default(0);
				$table->tinyInteger('groupwrite')->default(0);
				$table->tinyInteger('publicread')->default(0);
				$table->tinyInteger('publicwrite')->default(0);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->dateTime('datetimeconfigured');
				$table->tinyInteger('autouser')->default(0);
				$table->integer('files')->default(0);
				$table->integer('autouserunixgroupid')->default(0)->comment('FK to unixgroups.id');
				$table->integer('storageresourceid')->default(0)->comment('FK to storageresources.id');
				$table->index(['resourceid', 'groupid', 'datetimeremoved', 'datetimecreated'], 'resource');
				$table->index('parentstoragedirid');
				$table->index('resourceid');
				$table->index('groupid');
			});
		}

		if (!Schema::hasTable('storagedirquotanotificationtypes'))
		{
			Schema::create('storagedirquotanotificationtypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 100);
				$table->smallInteger('defaulttimeperiodid')->unsigned()->default(0)->comment('FK to timeperiods.id');
				$table->tinyInteger('valuetype')->default(0);
			});
			
			$types = array(
				array(
					'name' => 'Usage Report',
					'defaulttimeperiodid' => 2,
					'valuetype' => 1,
				),
				array(
					'name' => 'Space Threshold - Value',
					'defaulttimeperiodid' => 0,
					'valuetype' => 2,
				),
				array(
					'name' => 'Space Threshold - Percent',
					'defaulttimeperiodid' => 0,
					'valuetype' => 3,
				),
				array(
					'name' => 'File Threshold - Value',
					'defaulttimeperiodid' => 0,
					'valuetype' => 4,
				),
				array(
					'name' => 'File Threshold - Percent',
					'defaulttimeperiodid' => 0,
					'valuetype' => 3,
				),
			);

			foreach ($types as $type)
			{
				DB::table('storagedirquotanotificationtypes')->insert($type);
			}
		}

		if (!Schema::hasTable('storagedirquotanotifications'))
		{
			Schema::create('storagedirquotanotifications', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('storagedirid')->unsigned()->default(0)->comment('FK to storagedirs.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->smallInteger('storagedirquotanotificationtypeid')->unsigned()->default(0)->comment('FK to storagedirquotanotificationtypes.id');
				$table->bigInteger('value')->default(0);
				$table->smallInteger('timeperiodid')->unsigned()->default(0)->comment('FK to timeperiods.id');
				$table->integer('periods')->default(0);
				$table->smallInteger('notice')->unsigned()->default(0);
				$table->dateTime('datetimelastnotify');
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->tinyInteger('enabled')->unsigned()->default(1);
				$table->index('userid');
				$table->index('storagedirquotanotificationtypeid');
				$table->index('storagedirid');
			});
		}

		if (!Schema::hasTable('storagedirpurchases'))
		{
			Schema::create('storagedirpurchases', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->bigInteger('bytes')->default(0);
				$table->integer('sellergroupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->string('comment', 2000);
				$table->index(['resourceid', 'groupid', 'datetimestop', 'datetimestart'], 'resourceid');
			});
		}

		if (!Schema::hasTable('storagedirloans'))
		{
			Schema::create('storagedirloans', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->dateTime('datetimestart');
				$table->dateTime('datetimestop');
				$table->bigInteger('bytes')->default(0);
				$table->integer('lendergroupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->string('comment', 2000);
				$table->index(['resourceid', 'groupid', 'datetimestop', 'datetimestart'], 'resourceid');
			});
		}

		if (!Schema::hasTable('storagedirusage'))
		{
			Schema::create('storagedirusage', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('storagedirid')->unsigned()->default(0)->comment('FK to storagedirs.id');
				$table->bigInteger('quota')->unsigned()->default(0);
				$table->bigInteger('filequota')->unsigned()->default(0);
				$table->bigInteger('space')->unsigned()->default(0);
				$table->bigInteger('files')->unsigned()->default(0);
				$table->dateTime('datetimerecorded');
				$table->integer('lastinterval')->unsigned()->default(0);
				$table->index('storagedirid');
				$table->index(['storagedirid', 'datetimerecorded'], 'storagedirusage');
			});
		}

		if (!Schema::hasTable('storageresources'))
		{
			Schema::create('storageresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 32);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->char('path', 255);
				$table->integer('parentresourceid')->unsigned()->default(0)->comment('Parent storageresources.id');
				$table->tinyInteger('import')->default(0);
				$table->tinyInteger('autouserdir')->default(0);
				$table->bigInteger('defaultquotaspace')->default(0);
				$table->bigInteger('defaultquotafile')->default(0);
				$table->char('importhostname', 64);
				$table->integer('getquotatypeid')->unsigned()->default(0);
				$table->integer('createtypeid')->unsigned()->default(0);
				$table->index('parentresourceid');
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
			'storagedirs',
			'storagedirquotanotificationtypes',
			'storagedirquotanotification',
			'storagedirpurchases',
			'storagedirloans',
			'storagedirusage',
			'storageresources',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
