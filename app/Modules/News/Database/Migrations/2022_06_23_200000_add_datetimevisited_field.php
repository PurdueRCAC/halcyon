<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatetimevisitedField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('newsassociations') && !Schema::hasColumn('newsassociations', 'datetimevisited'))
		{
			// ALTER TABLE `newsassociations` ADD COLUMN `datetimevisited` DATETIME AFTER datetimeremoved;
			Schema::table('newsassociations', function (Blueprint $table)
			{
				$table->dateTime('datetimevisited')->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('newsassociations') && Schema::hasColumn('newsassociations', 'datetimevisited'))
		{
			Schema::table('newsassociations', function (Blueprint $table)
			{
				$table->dropColumn('datetimevisited');
			});
		}
	}
}
