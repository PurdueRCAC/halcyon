<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnabledColumn extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('users'))
		{
			if (!Schema::hasColumn('users', 'enabled'))
			{
				//ALTER TABLE ``users` ADD COLUMN `enabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `api_token`;
				Schema::table('users', function (Blueprint $table)
				{
					$table->tinyInteger('enabled')->unsigned()->default(1);
				});
			}
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
			if (Schema::hasColumn('users', 'enabled'))
			{
				Schema::table('users', function (Blueprint $table)
				{
					$table->dropColumn('enabled');
				});
			}
		}
	}
}
