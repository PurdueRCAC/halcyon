<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFilenameToPublicationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('publicaitons') && !Schema::hasColumn('publicaitons', 'filename'))
		{
			// ALTER TABLE `publicaitons` ADD COLUMN `filename` varchar(255) DEFAULT NULL;
			Schema::table('publicaitons', function (Blueprint $table)
			{
				$table->string('filename', 255);
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
		if (Schema::hasTable('publicaitons') && Schema::hasColumn('publicaitons', 'filename'))
		{
			Schema::table('publicaitons', function (Blueprint $table)
			{
				$table->dropColumn('filename');
			});
		}
	}
}
