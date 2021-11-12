<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNewidColumn extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('groupfieldofscience'))
		{
			if (Schema::hasColumn('groupfieldofscience', 'newid'))
			{
				Schema::table('groupfieldofscience', function (Blueprint $table)
				{
					$table->dropColumn('newid');
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
		if (Schema::hasTable('groupfieldofscience'))
		{
			if (!Schema::hasColumn('groupfieldofscience', 'newid'))
			{
				Schema::table('groupfieldofscience', function (Blueprint $table)
				{
					$table->integer('newid')->unsigned()->default(0);
				});
			}
		}
	}
}
