<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifiedEmailField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('userusernames') && !Schema::hasColumn('userusernames', 'dateverified'))
		{
			// ALTER TABLE `userusernames` ADD COLUMN `dateverified` DATETIME DEFAULT NULL;
			Schema::table('userusernames', function (Blueprint $table)
			{
				$table->dateTime('dateverified')->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('userusernames') && Schema::hasColumn('userusernames', 'dateverified'))
		{
			Schema::table('userusernames', function (Blueprint $table)
			{
				$table->dropColumn('dateverified');
			});
		}
	}
}
