<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropContactreportstems extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('contactreportstems');
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (!Schema::hasTable('contactreportstems'))
		{
			Schema::create('contactreportstems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('stemmedtext', 12000);
			});

			DB::statement('ALTER TABLE contactreportstems ADD FULLTEXT (stemmedtext)');
		}
	}
}
