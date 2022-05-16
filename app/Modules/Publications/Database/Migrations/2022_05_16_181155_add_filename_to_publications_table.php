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
		if (Schema::hasTable('publications') && !Schema::hasColumn('publications', 'filename'))
		{
			// ALTER TABLE `publications` ADD COLUMN `filename` varchar(255) DEFAULT NULL;
			Schema::table('publications', function (Blueprint $table)
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
		if (Schema::hasTable('publications') && Schema::hasColumn('publications', 'filename'))
		{
			Schema::table('publications', function (Blueprint $table)
			{
				$table->dropColumn('filename');
			});
		}
	}
}
