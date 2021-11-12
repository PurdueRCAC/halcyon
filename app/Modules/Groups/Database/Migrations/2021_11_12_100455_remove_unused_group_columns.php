<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedGroupColumns extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('groups'))
		{
			if (Schema::hasColumn('groups', 'deptnumber'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('deptnumber');
				});
			}

			if (Schema::hasColumn('groups', 'onepurdue'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('onepurdue');
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
			if (!Schema::hasColumn('groups', 'deptnumber'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->integer('deptnumber')->unsigned()->default(0)->comment('FK to collegedept.id');
				});
			}

			if (!Schema::hasColumn('groups', 'onepurdue'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->integer('onepurdue')->unsigned()->default(0);
				});
			}
		}
	}
}
