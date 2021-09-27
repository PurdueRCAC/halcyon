<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNewsDatesNullable extends Migration
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

		if (Schema::hasTable('news'))
		{
			Schema::table('news', function (Blueprint $table)
			{
				$table->dateTime('datetimenews')->nullable()->change();
				$table->dateTime('datetimenewsend')->nullable()->change();
				$table->dateTime('datetimeupdate')->nullable()->change();
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeedited')->nullable()->change();
				$table->dateTime('datetimemailed')->nullable()->change();
			});

			DB::table('news')
				->where('datetimenews', '=', '0000-00-00 00:00:00')
				->update([
					'datetimenews' => null
				]);

			DB::table('news')
				->where('datetimenewsend', '=', '0000-00-00 00:00:00')
				->update([
					'datetimenewsend' => null
				]);

			DB::table('news')
				->where('datetimeupdate', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeupdate' => null
				]);

			DB::table('news')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('news')
				->where('datetimeedited', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeedited' => null
				]);

			DB::table('news')
				->where('datetimemailed', '=', '0000-00-00 00:00:00')
				->update([
					'datetimemailed' => null
				]);
		}

		if (Schema::hasTable('newsassociations'))
		{
			Schema::table('newsassociations', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('newsassociations')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('newsassociations')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('newsupdates'))
		{
			Schema::table('newsupdates', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeedited')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('newsupdates')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('newsupdates')
				->where('datetimeedited', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeedited' => null
				]);

			DB::table('newsupdates')
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

		if (Schema::hasTable('news'))
		{
			Schema::table('news', function (Blueprint $table)
			{
				$table->dateTime('datetimenews')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimenewsend')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeupdate')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeedited')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimemailed')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('news')
				->whereNull('datetimenews')
				->update([
					'datetimenews' => '0000-00-00 00:00:00'
				]);

			DB::table('news')
				->whereNull('datetimenewsend')
				->update([
					'datetimenewsend' => '0000-00-00 00:00:00'
				]);

			DB::table('news')
				->whereNull('datetimeupdate')
				->update([
					'datetimeupdate' => '0000-00-00 00:00:00'
				]);

			DB::table('news')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('news')
				->whereNull('datetimeedited')
				->update([
					'datetimeedited' => '0000-00-00 00:00:00'
				]);

			DB::table('news')
				->whereNull('datetimemailed')
				->update([
					'datetimemailed' => '0000-00-00 00:00:00'
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
