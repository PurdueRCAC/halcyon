<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceunitsColumns extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('queueloans'))
		{
			if (!Schema::hasColumn('queueloans', 'serviceunits'))
			{
				//ALTER TABLE `queueloans` ADD COLUMN `serviceunits` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
				Schema::table('queueloans', function (Blueprint $table)
				{
					$table->float('serviceunits', 10, 2)->default(0.00);
				});
			}
		}

		if (Schema::hasTable('queuesizes'))
		{
			if (!Schema::hasColumn('queuesizes', 'serviceunits'))
			{
				//ALTER TABLE `queuesizess` ADD COLUMN `serviceunits` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
				Schema::table('queuesizes', function (Blueprint $table)
				{
					$table->float('serviceunits', 10, 2)->default(0.00);
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
		if (Schema::hasTable('queueloans'))
		{
			if (Schema::hasColumn('queueloans', 'serviceunits'))
			{
				Schema::table('queueloans', function (Blueprint $table)
				{
					$table->dropColumn('serviceunits');
				});
			}
		}

		if (Schema::hasTable('queuesizes'))
		{
			if (Schema::hasColumn('queuesizes', 'serviceunits'))
			{
				Schema::table('queuesizes', function (Blueprint $table)
				{
					$table->dropColumn('serviceunits');
				});
			}
		}
	}
}
