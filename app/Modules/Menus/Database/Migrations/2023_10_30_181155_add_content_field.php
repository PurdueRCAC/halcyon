<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentField extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'content'))
		{
			Schema::table('menu_items', function (Blueprint $table)
			{
				$table->dropColumn('note');
				$table->text('content')->nullable();
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
		if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'note'))
		{
			Schema::table('menu_items', function (Blueprint $table)
			{
				$table->dropColumn('content');
				$table->string('note', 255)->nullable();
			});
		}
	}
}
