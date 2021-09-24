<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCrmDatesNullable extends Migration
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

		if (Schema::hasTable('contactreports'))
		{
			Schema::table('contactreports', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimecontact')->nullable()->change();
				$table->dateTime('datetimegroupid')->nullable()->change();
			});

			DB::table('contactreports')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('contactreports')
				->where('datetimecontact', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecontact' => null
				]);

			DB::table('contactreports')
				->where('datetimegroupid', '=', '0000-00-00 00:00:00')
				->update([
					'datetimegroupid' => null
				]);
		}

		if (Schema::hasTable('contactreportcomments'))
		{
			Schema::table('contactreportcomments', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
			});

			DB::table('contactreportcomments')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);
		}

		if (Schema::hasTable('contactreportusers'))
		{
			Schema::table('contactreportusers', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimelastnotify')->nullable()->change();
			});

			DB::table('contactreportusers')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('contactreportusers')
				->where('datetimelastnotify', '=', '0000-00-00 00:00:00')
				->update([
					'datetimelastnotify' => null
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

		if (Schema::hasTable('contactreports'))
		{
			Schema::table('contactreports', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimecontact')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimegroupid')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('contactreports')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('contactreports')
				->whereNull('datetimecontact')
				->update([
					'datetimecontact' => '0000-00-00 00:00:00'
				]);

			DB::table('contactreports')
				->whereNull('datetimegroupid')
				->update([
					'datetimegroupid' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('contactreportcomments'))
		{
			Schema::table('contactreportcomments', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('contactreportcomments')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('contactreportusers'))
		{
			Schema::table('contactreportusers', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimelastnotify')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('contactreportusers')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('contactreportusers')
				->whereNull('datetimelastnotify')
				->update([
					'datetimelastnotify' => '0000-00-00 00:00:00'
				]);
		}

		DB::statement("SET sql_mode='$mode'");
	}
}
