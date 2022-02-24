<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFacetTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		// Resources
		if (!Schema::hasTable('resource_facet_types'))
		{
			Schema::create('resource_facet_types', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('type_id')->unsigned()->default(0);
				$table->char('type', 64);
				$table->string('name', 255);
				$table->string('label', 255);
				$table->string('placeholder', 255)->nullable();
				$table->string('description', 2000)->nullable();
				$table->string('default_value', 255)->nullable();
				$table->integer('ordering')->unsigned()->default(0);
				$table->tinyInteger('required')->default(0);
				$table->integer('min')->unsigned()->default(0);
				$table->integer('max')->unsigned()->default(0);
				$table->index('type_id');
				$table->index('type');
			});
		}

		if (!Schema::hasTable('resource_facet_type_options'))
		{
			Schema::create('resource_facet_type_options', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('facet_type_id')->unsigned()->default(0);
				$table->string('value', 255)->nullable();
				$table->string('label', 255);
				$table->integer('ordering')->unsigned()->default(0);
				$table->tinyInteger('checked')->unsigned()->default(0);
				$table->index('facet_type_id');
			});
		}

		if (!Schema::hasTable('resource_facets'))
		{
			Schema::create('resource_facets', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('facet_type_id')->unsigned()->default(0);
				$table->integer('asset_id')->unsigned()->default(0);
				$table->string('value', 512)->nullable();
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('asset_id');
				$table->index('facet_type_id');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$tables = array(
			'resource_facet_types',
			'resource_facet_type_options',
			'resource_facets'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
