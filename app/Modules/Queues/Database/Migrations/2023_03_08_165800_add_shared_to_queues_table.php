<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSharedToQueuesTable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up(): void
	{
		if (Schema::hasTable('queues'))
		{
			if (!Schema::hasColumn('queues', 'shared'))
			{
				//ALTER TABLE `queues` ADD COLUMN `shared` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0;
				Schema::table('queues', function (Blueprint $table)
				{
					$table->tinyInteger('shared')->unsigned()->default(0);
				});
			}
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down(): void
	{
		if (Schema::hasTable('queues'))
		{
			if (Schema::hasColumn('queues', 'shared'))
			{
				Schema::table('queues', function (Blueprint $table)
				{
					$table->dropColumn('shared');
				});
			}
		}
	}
}
