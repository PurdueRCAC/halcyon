<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration script for adding meta fields to pages
 **/
class AddMetaFields extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (Schema::hasTable('kb_pages'))
		{
			if (!Schema::hasColumn('kb_pages', 'metakey'))
			{
				Schema::table('kb_pages', function (Blueprint $table)
				{
					$table->tinyText('metakey')->nullable();
				});
			}

			if (!Schema::hasColumn('kb_pages', 'metadesc'))
			{
				Schema::table('kb_pages', function (Blueprint $table)
				{
					$table->tinyText('metadesc')->nullable();
				});
			}
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if (Schema::hasTable('kb_pages'))
		{
			if (Schema::hasColumn('kb_pages', 'metakey'))
			{
				Schema::table('kb_pages', function (Blueprint $table)
				{
					$table->dropColumn('metakey');
				});
			}

			if (Schema::hasColumn('kb_pages', 'metadesc'))
			{
				Schema::table('kb_pages', function (Blueprint $table)
				{
					$table->dropColumn('metadesc');
				});
			}
		}
	}
}
