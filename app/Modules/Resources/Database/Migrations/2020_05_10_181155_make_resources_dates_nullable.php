<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeResourcesDatesNullable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('resources'))
		{
			/*
			1. This requires doctrine/dbal to be installed
			2. Doctrine will throw an exception for column type 'timestamp'
			Schema::table('resources', function (Blueprint $table)
			{
				$table->timestamp('datetimecreated')->nullable()->change();
				$table->timestamp('datetimeremoved')->nullable()->change();
			});

			DB::table('resources')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('resources')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
			

			DB::statement("ALTER TABLE `resources` CHANGE `datetimecreated` `datetimecreated` DATETIME  NULL  DEFAULT NULL");
			//DB::statement("UPDATE `resources` SET `datetimecreated`=NULL WHERE `datetimecreated`='0000-00-00 00:00:00';");
			DB::statement("ALTER TABLE `resources` CHANGE `datetimeremoved` `datetimeremoved` DATETIME  NULL  DEFAULT NULL");
			//DB::statement("UPDATE `resources` SET `datetimeremoved`=NULL WHERE `datetimeremoved`='0000-00-00 00:00:00';");*/
		}

		if (Schema::hasTable('subresources'))
		{
			/*
			1. This requires doctrine/dbal to be installed
			2. Doctrine will throw an exception for column type 'timestamp'
			Schema::table('subresources', function (Blueprint $table)
			{
				$table->timestamp('datetimecreated')->nullable()->change();
				$table->timestamp('datetimeremoved')->nullable()->change();
			});

			DB::table('subresources')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('subresources')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
			

			DB::statement("UPDATE `subresources` SET `datetimecreated`='1980-01-01 00:00:00' WHERE `datetimecreated`='0000-00-00 00:00:00';");
			//DB::statement("ALTER TABLE subresources ALTER COLUMN `datetimecreated` SET DEFAULT NULL");
			DB::statement("ALTER TABLE `subresources` CHANGE `datetimecreated` `datetimecreated` DATETIME  NULL  DEFAULT NULL");
			DB::statement("UPDATE `subresources` SET `datetimecreated`=NULL WHERE `datetimecreated`='1980-01-01 00:00:00';");

			DB::statement("UPDATE `subresources` SET `datetimeremoved`='1980-01-01 00:00:00' WHERE `datetimeremoved`='0000-00-00 00:00:00';");
			//DB::statement("ALTER TABLE subresources ALTER COLUMN `datetimeremoved` SET DEFAULT NULL");
			DB::statement("ALTER TABLE `subresources` CHANGE `datetimeremoved` `datetimeremoved` DATETIME  NULL  DEFAULT NULL");
			DB::statement("UPDATE `subresources` SET `datetimeremoved`=NULL WHERE `datetimeremoved`='1980-01-01 00:00:00';");
			//DB::statement("UPDATE `subresources` SET `datetimeremoved`=NULL WHERE `datetimeremoved`='0000-00-00 00:00:00';");*/
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		DB::table('resources')
			->whereNull('datetimecreated')
			->update([
				'datetimecreated' => '0000-00-00 00:00:00'
			]);

		DB::table('resources')
			->whereNull('datetimeremoved')
			->update([
				'datetimeremoved' => '0000-00-00 00:00:00'
			]);

		DB::table('subresources')
			->whereNull('datetimecreated')
			->update([
				'datetimecreated' => '0000-00-00 00:00:00'
			]);

		DB::table('subresources')
			->whereNull('datetimeremoved')
			->update([
				'datetimeremoved' => '0000-00-00 00:00:00'
			]);
	}
}
