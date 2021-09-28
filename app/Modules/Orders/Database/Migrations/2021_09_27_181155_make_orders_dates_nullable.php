<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeOrdersDatesNullable extends Migration
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

		if (Schema::hasTable('orders'))
		{
			Schema::table('orders', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('orders')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('orders')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('ordercategories'))
		{
			Schema::table('ordercategories', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('ordercategories')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('ordercategories')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('orderitems'))
		{
			Schema::table('orderitems', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
				$table->dateTime('datetimefulfilled')->nullable()->change();
			});

			DB::table('orderitems')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('orderitems')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);

			DB::table('orderitems')
				->where('datetimefulfilled', '=', '0000-00-00 00:00:00')
				->update([
					'datetimefulfilled' => null
				]);
		}

		if (Schema::hasTable('orderproducts'))
		{
			Schema::table('orderproducts', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
			});

			DB::table('orderproducts')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('orderproducts')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);
		}

		if (Schema::hasTable('orderpurchaseaccounts'))
		{
			Schema::table('orderpurchaseaccounts', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->nullable()->change();
				$table->dateTime('datetimeremoved')->nullable()->change();
				$table->dateTime('datetimeapproved')->nullable()->change();
				$table->dateTime('datetimedenied')->nullable()->change();
				$table->dateTime('datetimepaid')->nullable()->change();
				$table->dateTime('datetimepaymentdoc')->nullable()->change();
			});

			DB::table('orderpurchaseaccounts')
				->where('datetimecreated', '=', '0000-00-00 00:00:00')
				->update([
					'datetimecreated' => null
				]);

			DB::table('orderpurchaseaccounts')
				->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeremoved' => null
				]);

			DB::table('orderpurchaseaccounts')
				->where('datetimeapproved', '=', '0000-00-00 00:00:00')
				->update([
					'datetimeapproved' => null
				]);

			DB::table('orderpurchaseaccounts')
				->where('datetimedenied', '=', '0000-00-00 00:00:00')
				->update([
					'datetimedenied' => null
				]);

			DB::table('orderpurchaseaccounts')
				->where('datetimepaid', '=', '0000-00-00 00:00:00')
				->update([
					'datetimepaid' => null
				]);

			DB::table('orderpurchaseaccounts')
				->where('datetimepaymentdoc', '=', '0000-00-00 00:00:00')
				->update([
					'datetimepaymentdoc' => null
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

		if (Schema::hasTable('orders'))
		{
			Schema::table('orders', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('orders')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('orders')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('ordercategories'))
		{
			Schema::table('ordercategories', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('ordercategories')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('ordercategories')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('orderitems'))
		{
			Schema::table('orderitems', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimefulfilled')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('orderitems')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('orderitems')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);

			DB::table('orderitems')
				->whereNull('datetimefulfilled')
				->update([
					'datetimefulfilled' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('orderproducts'))
		{
			Schema::table('orderproducts', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('orderproducts')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('orderproducts')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);
		}

		if (Schema::hasTable('orderpurchaseaccounts'))
		{
			Schema::table('orderpurchaseaccounts', function (Blueprint $table)
			{
				$table->dateTime('datetimecreated')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeremoved')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimeapproved')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimedenied')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimepaid')->notNull()->default('0000-00-00 00:00:00')->change();
				$table->dateTime('datetimepaymentdoc')->notNull()->default('0000-00-00 00:00:00')->change();
			});

			DB::table('orderpurchaseaccounts')
				->whereNull('datetimecreated')
				->update([
					'datetimecreated' => '0000-00-00 00:00:00'
				]);

			DB::table('orderpurchaseaccounts')
				->whereNull('datetimeremoved')
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);

			DB::table('orderpurchaseaccounts')
				->whereNull('datetimeapproved')
				->update([
					'datetimeapproved' => '0000-00-00 00:00:00'
				]);

			DB::table('orderpurchaseaccounts')
				->whereNull('datetimedenied')
				->update([
					'datetimedenied' => '0000-00-00 00:00:00'
				]);

			DB::table('orderpurchaseaccounts')
				->whereNull('datetimepaid')
				->update([
					'datetimepaid' => '0000-00-00 00:00:00'
				]);

			DB::table('orderpurchaseaccounts')
				->whereNull('datetimepaymentdoc')
				->update([
					'datetimepaymentdoc' => '0000-00-00 00:00:00'
				]);
		}

		DB::statement("SET sql_mode='$mode'");
	}
}
