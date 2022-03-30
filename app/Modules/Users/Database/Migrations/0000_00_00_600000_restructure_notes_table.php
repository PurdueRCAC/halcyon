<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructureNotesTable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('user_notes'))
		{
			if (Schema::hasColumn('user_notes', 'category_id'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('category_id');
				});
			}
			if (Schema::hasColumn('user_notes', 'review_time'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('review_time');
				});
			}
			if (Schema::hasColumn('user_notes', 'checked_out'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('checked_out');
				});
			}
			if (Schema::hasColumn('user_notes', 'checked_out_time'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('checked_out_time');
				});
			}
			if (Schema::hasColumn('user_notes', 'state'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('state');
				});
			}
			if (Schema::hasColumn('user_notes', 'subject'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('subject');
				});
			}
			if (Schema::hasColumn('user_notes', 'publish_up'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('publish_up');
				});
			}
			if (Schema::hasColumn('user_notes', 'publish_down'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dropColumn('publish_down');
				});
			}

			if (!Schema::hasColumn('user_notes', 'deleted_at'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dateTime('deleted_at')->nullable();
				});
			}
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('user_notes'))
		{
			if (!Schema::hasColumn('user_notes', 'publish_up'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dateTime('publish_up')->nullable();
				});
			}
			if (!Schema::hasColumn('user_notes', 'publish_down'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dateTime('publish_down')->nullable();
				});
			}
			if (!Schema::hasColumn('user_notes', 'subject'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->string('subject', 100)->default('');
				});
			}
			if (!Schema::hasColumn('user_notes', 'state'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->tinyInteger('state')->unsigned()->default(0);
				});
			}
			if (!Schema::hasColumn('user_notes', 'category_id'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->integer('category_id')->unsigned()->default(0);
				});
			}
			if (!Schema::hasColumn('user_notes', 'checked_out'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->integer('checked_out')->unsigned()->default(0);
				});
			}
			if (!Schema::hasColumn('user_notes', 'checked_out_time'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dateTime('checked_out_time')->nullable();
				});
			}
			if (!Schema::hasColumn('user_notes', 'review_time'))
			{
				Schema::table('user_notes', function (Blueprint $table)
				{
					$table->dateTime('review_time');
				});
			}
		}
	}
}
