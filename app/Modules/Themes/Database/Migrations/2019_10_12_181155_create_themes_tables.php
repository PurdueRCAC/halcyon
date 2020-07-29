<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateThemesTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('themes'))
		{
			Schema::create('themes', function (Blueprint $table)
			{
				//$table->engine = 'InnoDB';
				$table->increments('id');
				$table->string('template', 50)->default('');
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->tinyInteger('home')->unsigned()->default(0);
				$table->string('title', 255)->default('');
				$table->text('params')->nullable();
				$table->index('template');
				$table->index('home');
			});
			$this->info('Created `themes` table.');
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('themes');
		$this->info('Dropped `themes` table.');
	}
}
