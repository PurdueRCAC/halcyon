<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartTable extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up()
	{
		if (!Schema::hasTable('ordercarts'))
		{
			Schema::create('ordercarts', function (Blueprint $table)
			{
				$table->string('identifier');
				$table->string('instance');
				$table->longText('content');
				$table->nullableTimestamps();

				$table->primary(['identifier', 'instance']);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop('ordercarts');
	}
}
