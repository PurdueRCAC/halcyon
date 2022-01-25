<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('users') && !Schema::hasColumn('users', 'password'))
		{
			// ALTER TABLE `users` ADD COLUMN `password` varchar(255) DEFAULT NULL;
			Schema::table('users', function (Blueprint $table)
			{
				$table->string('password', 255)->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('users') && Schema::hasColumn('users', 'password'))
		{
			Schema::table('users', function (Blueprint $table)
			{
				$table->dropColumn('password');
			});
		}
	}
}
