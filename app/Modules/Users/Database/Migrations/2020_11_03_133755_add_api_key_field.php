<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddApiKeyField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('users'))
		{
			Schema::table('users', function (Blueprint $table)
			{
				$table->string('api_token', 100);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('users'))
		{
			Schema::table('users', function (Blueprint $table)
			{
				$table->dropColumn('api_token');
			});
		}
	}
}
