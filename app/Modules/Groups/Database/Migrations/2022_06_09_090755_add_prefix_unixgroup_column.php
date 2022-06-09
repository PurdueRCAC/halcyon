<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrefixUnixgroupColumn extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('groups'))
		{
			if (!Schema::hasColumn('groups', 'prefix_unixgroup'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					// ALTER TABLE `groups` ADD `prefix_unixgroup` TINYINT  UNSIGNED  NOT NULL  DEFAULT '1';
					$table->tinyInteger('prefix_unixgroup')->unsigned()->default(1);
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
		if (Schema::hasTable('groups'))
		{
			if (Schema::hasColumn('groups', 'prefix_unixgroup'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('prefix_unixgroup');
				});
			}
		}
	}
}
