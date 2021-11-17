<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('userusernames') && !Schema::hasColumn('userusernames', 'email'))
		{
			// ALTER TABLE `userusernames` ADD COLUMN `email` varchar(255) DEFAULT NULL;
			Schema::table('userusernames', function (Blueprint $table)
			{
				$table->string('email', 255)->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('userusernames') && Schema::hasColumn('userusernames', 'email'))
		{
			Schema::table('userusernames', function (Blueprint $table)
			{
				$table->dropColumn('email');
			});
		}
	}
}
