<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentField extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('newsassociations') && !Schema::hasColumn('newsassociations', 'comment'))
		{
			// ALTER TABLE `newsassociations` ADD COLUMN `comment` varchar(2000) DEFAULT NULL;
			Schema::table('newsassociations', function (Blueprint $table)
			{
				$table->string('comment', 2000);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('newsassociations') && Schema::hasColumn('newsassociations', 'comment'))
		{
			Schema::table('newsassociations', function (Blueprint $table)
			{
				$table->dropColumn('comment');
			});
		}
	}
}
