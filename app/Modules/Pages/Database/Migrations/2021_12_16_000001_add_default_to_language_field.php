<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDefaultToLanguageField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'language'))
		{
			// Set the default value
			Schema::table('pages', function (Blueprint $table)
			{
				// ALTER TABLE `pages` CHANGE COLUMN `language` `language` VARCHAR(7) NOT NULL DEFAULT '*';
				$table->string('language', 7)->default('*')->change();
			});

			DB::table('pages')->where('language', '=', '')->update(['language' => '*']);
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'language'))
		{
			// ALTER TABLE `pages` CHANGE COLUMN `language` `language` VARCHAR(7) NOT NULL DEFAULT '';
			Schema::table('pages', function (Blueprint $table)
			{
				$table->string('language', 7)->change();
			});
		}
	}
}
