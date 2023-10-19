<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAccessField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('resources'))
		{
			if (!Schema::hasColumn('resources', 'access'))
			{
				//ALTER TABLE `resources` ADD COLUMN `access` int(11) unsigned NOT NULL DEFAULT 0;
				Schema::table('resources', function (Blueprint $table)
				{
					$table->integer('access')->unsigned()->default(0)->comment('FK to viewlevels.id');
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
		if (Schema::hasTable('resources'))
		{
			if (Schema::hasColumn('resources', 'access'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->dropColumn('access');
				});
			}
		}
	}
}
