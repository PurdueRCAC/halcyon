<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptions extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		// Resources
		if (Schema::hasTable('resources'))
		{
			if (!Schema::hasColumn('resources', 'description'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->string('description', 2000)->nullable();
				});
			}

			if (!Schema::hasColumn('resources', 'params'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->string('status', 2000)->nullable();
				});
			}

			if (!Schema::hasColumn('resources', 'status'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->string('status', 50)->nullable();
				});
			}
		}

		// Resource types
		if (Schema::hasTable('resourcetypes') && !Schema::hasColumn('resourcetypes', 'description'))
		{
			Schema::table('resourcetypes', function (Blueprint $table)
			{
				$table->string('description', 2000)->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('resources'))
		{
			if (Schema::hasColumn('resources', 'description'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->dropColumn('description');
				});
			}

			if (Schema::hasColumn('resources', 'params'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->dropColumn('params');
				});
			}

			if (Schema::hasColumn('resources', 'status'))
			{
				Schema::table('resources', function (Blueprint $table)
				{
					$table->dropColumn('status');
				});
			}
		}

		if (Schema::hasTable('resourcetypes') && Schema::hasColumn('resourcetypes', 'description'))
		{
			Schema::table('resourcetypes', function (Blueprint $table)
			{
				$table->dropColumn('description');
			});
		}
	}
}
