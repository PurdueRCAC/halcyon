<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseBodyLength extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('news') && Schema::hasColumn('news', 'body'))
		{
			Schema::table('news', function (Blueprint $table)
			{
				$table->text('body')->change();
			});
		}

		if (Schema::hasTable('newsstemmedtext') && Schema::hasColumn('newsstemmedtext', 'stemmedtext'))
		{
			Schema::table('newsstemmedtext', function (Blueprint $table)
			{
				$table->text('stemmedtext')->change();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('news') && Schema::hasColumn('news', 'body'))
		{
			Schema::table('news', function (Blueprint $table)
			{
				$table->string('body', 15000)->change();
			});
		}

		if (Schema::hasTable('newsstemmedtext') && Schema::hasColumn('newsstemmedtext', 'stemmedtext'))
		{
			Schema::table('newsstemmedtext', function (Blueprint $table)
			{
				$table->string('stemmedtext', 16200)->change();
			});
		}
	}
}
