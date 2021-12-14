<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupmanagedField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('storageresources') && !Schema::hasColumn('storageresources', 'groupmanaged'))
		{
			// ALTER TABLE `storageresources` ADD COLUMN `groupmanaged` tinyint(3) UNSIGNED DEFAULT '0';
			Schema::table('storageresources', function (Blueprint $table)
			{
				$table->tinyInteger('groupmanaged')->unsigned()->default(0);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('storageresources') && Schema::hasColumn('storageresources', 'groupmanaged'))
		{
			Schema::table('storageresources', function (Blueprint $table)
			{
				$table->dropColumn('groupmanaged');
			});
		}
	}
}
