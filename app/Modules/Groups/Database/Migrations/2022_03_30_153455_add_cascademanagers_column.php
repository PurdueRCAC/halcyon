<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascademanagersColumn extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('groups'))
		{
			if (!Schema::hasColumn('groups', 'cascademanagers'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					// ALTER TABLE `groups` ADD `cascademanagers` TINYINT  UNSIGNED  NOT NULL  DEFAULT '1';
					$table->tinyInteger('cascademanagers')->unsigned()->default(1);
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
			if (Schema::hasColumn('groups', 'cascademanagers'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('cascademanagers');
				});
			}
		}
	}
}
