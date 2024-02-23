<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddRegistrationFieldsColumn extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('registration_fields'))
		{
			Schema::table('registration_fields', function (Blueprint $table)
			{
				$table->json('options')->nullable();
			});
		}

	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('registration_fields'))
		{
			Schema::table('registration_fields', function (Blueprint $table)
			{
				$table->dropColumn('options');
			});
		}
	}
}
