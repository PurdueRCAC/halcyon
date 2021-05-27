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
		if (Schema::hasTable('users') && !Schema::hasColumn('users', 'api_token'))
		{
			// ALTER TABLE `users` ADD COLUMN `api_token` varchar(100) DEFAULT NULL;
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
		if (Schema::hasTable('users') && Schema::hasColumn('users', 'api_token'))
		{
			Schema::table('users', function (Blueprint $table)
			{
				$table->dropColumn('api_token');
			});
		}
	}
}
