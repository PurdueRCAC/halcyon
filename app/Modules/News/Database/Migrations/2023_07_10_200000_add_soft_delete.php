<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDelete extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('news') && !Schema::hasColumn('news', 'datetimeremoved'))
		{
			Schema::table('news', function (Blueprint $table)
			{
				$table->dateTime('datetimeremoved')->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('news') && Schema::hasColumn('news', 'datetimeremoved'))
		{
			Schema::table('nnews', function (Blueprint $table)
			{
				$table->dropColumn('datetimeremoved');
			});
		}
	}
}
