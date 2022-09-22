<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionColumn extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('groups'))
		{
			if (!Schema::hasColumn('groups', 'description'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					// ALTER TABLE `groups` ADD `description` VARCHAR(2000);
					$table->string('description', 2000);
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
			if (Schema::hasColumn('groups', 'description'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('description');
				});
			}
		}
	}
}
