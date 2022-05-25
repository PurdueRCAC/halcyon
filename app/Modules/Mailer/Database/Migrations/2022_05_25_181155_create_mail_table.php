<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateMailTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('mail_messages'))
		{
			Schema::create('mail_messages', function (Blueprint $table)
			{
				$table->increments('id');
				$table->tinyInteger('template')->unsigned()->default(0);
				$table->string('subject', 255);
				$table->mediumText('body');
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->dateTime('sent_at')->nullable();
				$table->integer('sent_by')->unsigned()->default(0);
				$table->mediumText('recipients')->nullable();
				$table->index('template');
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
		Schema::dropIfExists('mail_messages');
	}
}
