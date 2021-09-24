<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIssuesDatesNullable extends Migration
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

		if (Schema::hasTable('issues'))
		{
			Schema::table('issues', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('issues')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('issues')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('issuecomments'))
		{
			Schema::table('issuecomments', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('issuecomments')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('issuecomments')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('issuetodos'))
		{
			Schema::table('issuetodos', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('issuetodos')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('issuetodos')
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

		if (Schema::hasTable('issues'))
		{
			Schema::table('issues', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('issues')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('issues')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('issuecomments'))
		{
			Schema::table('issuecomments', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('issuecomments')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('issuecomments')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('issuetodos'))
		{
			Schema::table('issuetodos', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('issuetodos')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('issuetodos')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		DB::statement("SET sql_mode='$mode'");
	}
}
