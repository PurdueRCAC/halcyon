<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinderTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('finder_facets'))
		{
			Schema::create('finder_facets', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 150);
				$table->string('control_type', 150);
				$table->integer('parent')->unsigned()->default(0);
				$table->integer('weight')->unsigned()->default(0);
				$table->tinyInteger('status')->unsigned()->default(0);
				$table->text('description');
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('parent');
			});
		}

		if (!Schema::hasTable('finder_fields'))
		{
			Schema::create('finder_fields', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 150);
				$table->string('label', 150);
				$table->integer('weight')->unsigned()->default(0);
				$table->tinyInteger('status')->unsigned()->default(0);
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('parent');
			});
		}

		if (!Schema::hasTable('finder_service_facets'))
		{
			Schema::create('finder_service_facets', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('service_id')->unsigned()->default(0);
				$table->integer('facet_id')->unsigned()->default(0);
				$table->index(['service_id', 'facet_id']);
			});
		}

		if (!Schema::hasTable('finder_service_fields'))
		{
			Schema::create('finder_service_fields', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('service_id')->unsigned()->default(0);
				$table->integer('field_id')->unsigned()->default(0);
				$table->text('value');
				$table->index(['service_id', 'field_id']);
			});
		}

		if (!Schema::hasTable('finder_services'))
		{
			Schema::create('finder_services', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 255);
				$table->string('summary', 1200);
				$table->tinyInteger('status')->unsigned()->default(0);
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
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
		$tables = array(
			'finder_facets',
			'finder_fields',
			'finder_service_facets',
			'finder_service_fields',
			'finder_services',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
