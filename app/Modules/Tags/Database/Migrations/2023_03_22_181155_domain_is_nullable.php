<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DomainIsNullable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('tags') && Schema::hasColumn('tags', 'domain'))
		{
			Schema::table('tags', function (Blueprint $table)
			{
				$table->string('domain', 100)->nullable()->change();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('tags') && Schema::hasColumn('tags', 'domain'))
		{
			Schema::table('tags', function (Blueprint $table)
			{
				$table->string('domain', 100)->change();
			});
		}
	}
}
