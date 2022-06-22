<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentidField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('newstypes') && !Schema::hasColumn('newstypes', 'parentid'))
		{
			// ALTER TABLE `newstypes` ADD COLUMN `parentid` integer(11) UNSIGNED NOT NULL DEFAULT '0';
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->integer('parentid')->unsigned()->default(0);
				$table->index('parentid');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('newstypes') && Schema::hasColumn('newstypes', 'parentid'))
		{
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->dropColumn('parentid');
			});
		}
	}
}
