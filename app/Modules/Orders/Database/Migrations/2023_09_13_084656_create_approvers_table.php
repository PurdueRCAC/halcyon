<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApproversTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('orderapprovers'))
		{
			Schema::create('orderapprovers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('Who the default approver is. FK to users.id');
				$table->integer('departmentid')->unsigned()->default(0)->comment('FK to collegedept.id');
				$table->index('userid');
				$table->index('departmentid');
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
		Schema::drop('orderapprovers');
	}
}
