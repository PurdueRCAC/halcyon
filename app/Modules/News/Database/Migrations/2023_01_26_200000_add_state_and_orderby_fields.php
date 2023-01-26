<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\News\Models\Type;

class AddStateAndOrderbyFields extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('newstypes') && !Schema::hasColumn('newstypes', 'state'))
		{
			// ALTER TABLE `newstypes` ADD COLUMN `state` VARCHAR(45) NULL DEFAULT NULL;
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->string('state', 45)->nullable();
			});
		}

		if (Schema::hasTable('newstypes') && !Schema::hasColumn('newstypes', 'order_by'))
		{
			// ALTER TABLE `newstypes` ADD COLUMN `order_by` VARCHAR(45) NULL DEFAULT NULL;
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->string('order_by', 45)->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('newstypes') && Schema::hasColumn('newstypes', 'state'))
		{
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->dropColumn('state');
			});
		}

		if (Schema::hasTable('newstypes') && Schema::hasColumn('newstypes', 'order_by'))
		{
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->dropColumn('order_by');
			});
		}
	}
}
