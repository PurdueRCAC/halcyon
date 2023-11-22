<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeniedreasonField extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('orderpurchaseaccounts'))
		{
			Schema::table('orderpurchaseaccounts', function (Blueprint $table)
			{
				$table->string('deniedreason', 2000)->nullable();
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
		if (Schema::hasTable('orderpurchaseaccounts') && Schema::hasColumn('orderpurchaseaccounts', 'deniedreason'))
		{
			Schema::table('orderpurchaseaccounts', function (Blueprint $table)
			{
				$table->dropColumn('deniedreason');
			});
		}
	}
}
