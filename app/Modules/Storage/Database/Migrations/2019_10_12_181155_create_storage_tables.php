<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
				$table->smallInteger('resourceid')->unsigned()->default(0);
				$table->integer('groupid')->unsigned()->default(0);
				$table->integer('parentstoragedirid')->unsigned()->default(0);
				$table->char('name', 32);
				$table->char('path', 255);
				$table->bigInteger('bytes')->default(0);
				$table->integer('owneruserid')->unsigned()->default(0);
				$table->integer('unixgroupid')->unsigned()->default(0);
				$table->tinyInteger('ownerread')->default(0);
				$table->tinyInteger('ownerwrite')->default(0);
				$table->tinyInteger('groupread')->default(0);
				$table->tinyInteger('groupwrite')->default(0);
				$table->tinyInteger('publicread')->default(0);
				$table->tinyInteger('publicwrite')->default(0);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->timestamp('datetimeconfigured');
				$table->tinyInteger('autouser')->default(0);
				$table->integer('files')->default(0);
				$table->integer('autouserunixgroupid')->default(0);
				$table->integer('storageresourceid')->default(0);
				$table->index(['resourceid', 'groupid', 'datetimeremoved', 'datetimecreated']);
				$table->index('parentstoragedirid');
				$table->index('resourceid');
				$table->index('groupid');
			});
			$this->info('Created `storagedirs` table.');
		}

		if (!Schema::hasTable('storagedirquotanotificationtypes'))
		{
			Schema::create('storagedirquotanotificationtypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 100);
				$table->smallInteger('defaulttimeperiodid')->unsigned()->default(0);
				$table->tinyInteger('valuetype')->default(0);
			});
			$this->info('Created `storagedirquotanotificationtypes` table.');
		}

		if (!Schema::hasTable('storagedirquotanotifications'))
		{
			Schema::create('storagedirquotanotifications', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('storagedirid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->smallInteger('storagedirquotanotificationtypeid')->unsigned()->default(0);
				$table->bigInteger('value')->default(0);
				$table->smallInteger('timeperiodid')->unsigned()->default(0);
				$table->integer('periods')->default(0);
				$table->smallInteger('notice')->unsigned()->default(0);
				$table->timestamp('datetimelastnotify');
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->tinyInteger('enabled')->unsigned()->default(0);
				$table->index('userid');
				$table->index('storagedirquotanotificationtypeid');
				$table->index('storagedirid');
			});
			$this->info('Created `storagedirquotanotification` table.');
		}

		if (!Schema::hasTable('storagedirpurchases'))
		{
			Schema::create('storagedirpurchases', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0);
				$table->integer('groupid')->unsigned()->default(0);
				$table->timestamp('datetimestart');
				$table->timestamp('datetimestop');
				$table->bigInteger('bytes')->default(0);
				$table->integer('sellergroupid')->unsigned()->default(0);
				$table->char('comment', 2000);
				$table->index(['resourceid', 'groupid', 'datetimestop', 'datetimestart']);
			});
			$this->info('Created `storagedirpurchases` table.');
		}

		if (!Schema::hasTable('storagedirloans'))
		{
			Schema::create('storagedirloans', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('resourceid')->unsigned()->default(0);
				$table->integer('groupid')->unsigned()->default(0);
				$table->timestamp('datetimestart');
				$table->timestamp('datetimestop');
				$table->bigInteger('bytes')->default(0);
				$table->integer('lendergroupid')->unsigned()->default(0);
				$table->char('comment', 2000);
				$table->index(['resourceid', 'groupid', 'datetimestop', 'datetimestart']);
			});
			$this->info('Created `storagedirloans` table.');
		}

		if (!Schema::hasTable('storagedirusage'))
		{
			Schema::create('storagedirusage', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('storagedirid')->unsigned()->default(0);
				$table->bigInteger('quota')->default(0);
				$table->bigInteger('filequota')->default(0);
				$table->bigInteger('space')->default(0);
				$table->bigInteger('files')->default(0);
				$table->timestamp('datetimerecorded');
				$table->integer('lastinterval')->unsigned()->default(0);
				$table->index('storagedirid');
				$table->index(['storagedirusage', 'datetimerecorded']);
			});
			$this->info('Created `storagedirusage` table.');
		}

		if (!Schema::hasTable('storageresources'))
		{
			Schema::create('storageresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 32);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->char('path', 255);
				$table->integer('parentresourceid')->unsigned()->default(0);
				$table->tinyInteger('import')->default(0);
				$table->tinyInteger('autouserdir')->default(0);
				$table->bigInteger('defaultquotaspace')->default(0);
				$table->bigInteger('defaultquotafile')->default(0);
				$table->char('importhostname', 64);
				$table->integer('getquotatypeid')->unsigned()->default(0);
				$table->integer('createtypeid')->unsigned()->default(0);
				$table->index('parentresourceid');
			});
			$this->info('Created `storageresources` table.');
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

			$this->info('Dropped `' . $table . '` table.');
		}
	}
}
