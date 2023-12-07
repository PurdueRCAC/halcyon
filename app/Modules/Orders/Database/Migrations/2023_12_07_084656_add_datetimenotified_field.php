<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatetimenotifiedField extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('orders'))
		{
			Schema::table('orders', function (Blueprint $table)
			{
				$table->dateTime('datetimenotified')->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'datetimenotified'))
		{
			Schema::table('orders', function (Blueprint $table)
			{
				$table->dropColumn('datetimenotified');
			});
		}
	}
}
