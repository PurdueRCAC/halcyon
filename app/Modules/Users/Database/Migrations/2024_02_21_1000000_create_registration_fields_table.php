<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateRegistrationFieldsTable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('registration_fields'))
		{
			Schema::create('registration_fields', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 128);
				$table->string('type', 128)->default('text');
                $table->boolean('required')->default(false);
                $table->boolean('include_admin')->default(false);
                $table->timestamps();
				$table->dateTime('deleted_at')->nullable();
				$table->index('name');
			});
		}

	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
	    Schema::dropIfExists('registration_fields');
	}
}
