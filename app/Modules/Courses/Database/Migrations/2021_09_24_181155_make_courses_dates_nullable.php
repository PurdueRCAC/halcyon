<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCoursesDatesNullable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		$vars = DB::select(DB::raw("SHOW VARIABLES LIKE 'sql_mode'"));
		$mode = $vars[0]->Value;

		DB::statement("SET sql_mode=''");

		if (Schema::hasTable('classaccounts'))
		{
			Schema::table('classaccounts', function (Blueprint $table)
			{
				$table->dateTime('datetimestart')->nullable()->change();
				$table->dateTime('datetimestop')->nullable()->change();
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('classaccounts')
				->where('datetimestart', '=', '0000-00-00 00:00:00')
				->update([
					'datetimestart' => null
				]);

			DB::table('classaccounts')
				->where('datetimestop', '=', '0000-00-00 00:00:00')
				->update([
					'datetimestop' => null
				]);

			DB::table('classaccounts')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('classaccounts')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('classusers'))
		{
			Schema::table('classusers', function (Blueprint $table)
			{
				$table->dateTime('datetimestart')->nullable()->change();
				$table->dateTime('datetimestop')->nullable()->change();
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('classusers')
				->where('datetimestart', '=', '0000-00-00 00:00:00')
				->update([
					'datetimestart' => null
				]);

			DB::table('classusers')
				->where('datetimestop', '=', '0000-00-00 00:00:00')
				->update([
					'datetimestop' => null
				]);

			DB::table('classusers')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('classusers')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		DB::statement("SET sql_mode='$mode'");
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$vars = DB::select(DB::raw("SHOW VARIABLES LIKE 'sql_mode'"));
		$mode = $vars[0]->Value;

		DB::statement("SET sql_mode=''");

		if (Schema::hasTable('classaccounts'))
		{
			Schema::table('classaccounts', function (Blueprint $table)
			{
				$table->dateTime('datetimestart')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimestop')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('classaccounts')
				->whereNull('datetimestart')
				->update([
					'datetimestart' => '0000-00-00 00:00:00'
				]);

			DB::table('classaccounts')
				->whereNull('datetimestop')
				->update([
					'datetimestop' => '0000-00-00 00:00:00'
				]);

			DB::table('classaccounts')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('classaccounts')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('classusers'))
		{
			Schema::table('classusers', function (Blueprint $table)
			{
				$table->dateTime('datetimestart')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimestop')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('classusers')
				->whereNull('datetimestart')
				->update([
					'datetimestart' => '0000-00-00 00:00:00'
				]);

			DB::table('classusers')
				->whereNull('datetimestop')
				->update([
					'datetimestop' => '0000-00-00 00:00:00'
				]);

			DB::table('classusers')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('classusers')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		DB::statement("SET sql_mode='$mode'");
	}
}
