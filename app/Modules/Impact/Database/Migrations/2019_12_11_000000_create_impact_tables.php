<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing impact tables
 **/
class CreateImpactTables extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!Schema::hasTable('awardflags'))
		{
			Schema::create('awardflags', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('puid')->unsigned()->default(0)->comment('Organization ID');
				$table->smallInteger('fiscalyear')->unsigned()->default(0);
				$table->char('flag', 128);
				$table->index(['puid', 'fiscalyear'], 'puid');
				$table->index(['fiscalyear', 'flag'], 'fiscalyear');
			});
		}

		if (!Schema::hasTable('awardreports'))
		{
			Schema::create('awardreports', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('fiscalyear')->unsigned()->default(0);
				$table->bigInteger('awards')->unsigned()->default(0);
				$table->bigInteger('totalawards')->unsigned()->default(0);
				$table->smallInteger('awardeecount')->unsigned()->default(0);
				$table->smallInteger('totalawardeecount')->unsigned()->default(0);
				$table->index('fiscalyear');
			});
		}

		if (!Schema::hasTable('awardresources'))
		{
			Schema::create('awardresources', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('name', 32);
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
			});
		}

		if (!Schema::hasTable('awards'))
		{
			Schema::create('awards', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('puid')->unsigned()->default(0);
				$table->smallInteger('fiscalyear')->unsigned()->default(0);
				$table->char('username', 16)->nullable();
				$table->char('name', 128)->nullable();
				$table->bigInteger('award')->unsigned()->default(0);
				$table->bigInteger('fa')->unsigned()->default(0);
				$table->index(['fiscalyear', 'puid'], 'fiscalyear');
				$table->index('puid');
			});
		}

		if (!Schema::hasTable('awards2'))
		{
			Schema::create('awards2', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('proposal')->unsigned()->default(0);
				$table->integer('puid')->unsigned()->default(0);
				$table->smallInteger('fiscalyear')->unsigned()->default(0);
				$table->char('username', 16);
				$table->char('namepi', 128);
				$table->char('name', 128);
				$table->char('role', 8);
				$table->bigInteger('total')->unsigned()->default(0);
				$table->bigInteger('award')->unsigned()->default(0);
				$table->bigInteger('fa')->unsigned()->default(0);
				$table->index(['fiscalyear', 'puid'], 'fiscalyear');
				$table->index('puid');
			});
		}

		if (!Schema::hasTable('impactconstants'))
		{
			Schema::create('impactconstants', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 255);
				$table->string('value', 255);
				$table->integer('impacttableid')->unsigned()->default(0);
				$table->integer('sequence')->unsigned()->default(0);
			});
		}

		if (!Schema::hasTable('impacts'))
		{
			Schema::create('impacts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 255);
				$table->string('value', 255);
				$table->integer('impacttableid')->unsigned()->default(0);
				$table->integer('sequence')->unsigned()->default(0);
				$table->dateTime('updatedatetime')->nullable();
			});
		}

		if (!Schema::hasTable('impacttables'))
		{
			Schema::create('impacttables', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 255);
				$table->string('columnname', 255);
				$table->integer('sequence')->unsigned()->default(0);
				$table->string('updatekey', 255);
			});
		}

		if (!Schema::hasTable('ownerawards'))
		{
			Schema::create('ownerawards', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('owneruserid')->unsigned()->default(0);
				$table->integer('puid')->unsigned()->default(0);
				$table->smallInteger('fiscalyear')->unsigned()->default(0);
				$table->char('resource', 16)->nullable();
				$table->bigInteger('award')->unsigned()->default(0);
				$table->index(['fiscalyear', 'resource'], 'fiscalyear');
				$table->index(['owneruserid', 'fiscalyear', 'resource'], 'owneruserid');
				$table->index(['puid', 'fiscalyear', 'resource'], 'puid');
			});
		}

		if (!Schema::hasTable('ownernodes'))
		{
			Schema::create('ownernodes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->char('year', 6);
				$table->integer('resourceid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('maxnodes')->unsigned()->default(0);
				$table->decimal('proratednodes', 9, 4)->unsigned()->default(0.0000);
				$table->tinyInteger('ownercount')->unsigned()->default(0);
				$table->index(['year', 'resourceid', 'userid'], 'year');
				$table->index(['userid', 'resourceid'], 'resourceid');
				$table->index(['userid', 'year'], 'userid');
			});
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tables = array(
			'awardflags',
			'awardreports',
			'awardresources',
			'awards',
			'awards2',
			'impactconstants',
			'impacts',
			'impacttables',
			'ownerawards'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
