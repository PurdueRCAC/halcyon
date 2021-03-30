<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptions extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('resources'))
		{
			Schema::table('resources', function (Blueprint $table)
			{
				$table->string('description', 2000)->nullable();
			});
		}

		if (Schema::hasTable('resourcetypes'))
		{
			Schema::table('resourcetypes', function (Blueprint $table)
			{
				$table->string('description', 2000)->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::table('resources', function (Blueprint $table)
		{
			$table->dropColumn('description');
		});

		Schema::table('resourcetypes', function (Blueprint $table)
		{
			$table->dropColumn('description');
		});
	}
}
