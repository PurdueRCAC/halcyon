<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('ordercarts'))
		{
			Schema::create('ordercarts', function (Blueprint $table)
			{
				$table->string('identifier')->comment('FK to userusernames.username');
				$table->string('instance');
				$table->longText('content');
				$table->nullableTimestamps();

				$table->primary(['identifier', 'instance']);
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
		Schema::drop('ordercarts');
	}
}
