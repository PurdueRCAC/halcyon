<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSoftwareTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('application_types'))
		{
			Schema::create('application_types', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 255);
				$table->string('alias', 255);
			});
		}

		if (!Schema::hasTable('applications'))
		{
			Schema::create('applications', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('type_id')->unsigned()->default(0)->comment('FK to application_types.id');
				$table->string('title', 255);
				$table->string('alias', 255);
				$table->text('description')->nullable();
				$table->tinyInteger('state')->unsigned()->default(0);
				$table->integer('access')->unsigned()->default(0);
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('state');
				$table->index('access');
			});
		}

		if (!Schema::hasTable('application_versions'))
		{
			Schema::create('application_versions', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('application_id')->unsigned()->default(0)->comment('FK to applications.id');
				$table->string('title', 255);
				$table->index('application_id');
			});
		}

		if (!Schema::hasTable('application_version_resourcess'))
		{
			Schema::create('application_version_resourcess', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('version_id')->unsigned()->default(0)->comment('FK to application_versions.id');
				$table->integer('resource_id')->unsigned()->default(0)->comment('FK to resources.id');
				$table->index(['version_id', 'resource_id']);
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
		Schema::dropIfExists('application_types');
		Schema::dropIfExists('applications');
		Schema::dropIfExists('application_versions');
		Schema::dropIfExists('application_version_resources');
	}
}
