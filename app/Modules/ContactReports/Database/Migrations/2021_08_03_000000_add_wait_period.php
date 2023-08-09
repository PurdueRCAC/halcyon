<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for adding wait period columns to table
 **/
class AddWaitPeriod extends Migration
{
	/**
	 * Up
	 **/
	public function up(): void
	{
		if (Schema::hasTable('contactreporttypes') && !Schema::hasColumn('contactreporttypes', 'waitperiodid'))
		{
			Schema::table('contactreporttypes', function (Blueprint $table)
			{
				$table->tinyInteger('waitperiodid')->unsigned()->default(0);
			});
		}

		if (Schema::hasTable('contactreporttypes') && !Schema::hasColumn('contactreporttypes', 'waitperiodcount'))
		{
			Schema::table('contactreporttypes', function (Blueprint $table)
			{
				$table->tinyInteger('waitperiodcount')->unsigned()->default(0);
			});
		}
	}

	/**
	 * Down
	 **/
	public function down(): void
	{
		if (Schema::hasTable('contactreporttypes') && Schema::hasColumn('contactreporttypes', 'waitperiodid'))
		{
			Schema::table('contactreporttypes', function (Blueprint $table)
			{
				$table->dropColumn('waitperiodid');
			});
		}

		if (Schema::hasTable('contactreporttypes') && Schema::hasColumn('contactreporttypes', 'waitperiodcount'))
		{
			Schema::table('contactreporttypes', function (Blueprint $table)
			{
				$table->dropColumn('waitperiodcount');
			});
		}
	}
}
