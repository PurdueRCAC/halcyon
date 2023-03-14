<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\News\Models\Type;

class AddAliasAndOrderingFields extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('newstypes') && !Schema::hasColumn('newstypes', 'ordering'))
		{
			// ALTER TABLE `newstypes` ADD COLUMN `ordering` INTEGER NOT NULL UNSIGNED DEFAULT '0';
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->integer('ordering')->unsigned()->default(0);
			});

			$types = Type::query()
				->where('parentid', '=', 0)
				->orderBy('id')
				->get();

			foreach ($types as $i => $type)
			{
				$type->ordering = ($i + 1);
				$type->saveQuietly();

				foreach ($type->children()->orderBy('id')->get() as $k => $child)
				{
					$child->ordering = ($k + 1);
					$child->saveQuietly();
				}
			}
		}

		if (Schema::hasTable('newstypes') && !Schema::hasColumn('newstypes', 'alias'))
		{
			// ALTER TABLE `newstypes` ADD COLUMN `alias` varchar(32) AFTER name;
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->string('alias', 32);
			});

			$types = Type::query()
				->get();

			foreach ($types as $i => $type)
			{
				$type->name = $type->name;
				$type->saveQuietly();
			}
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('newstypes') && Schema::hasColumn('newstypes', 'ordering'))
		{
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->dropColumn('ordering');
			});
		}

		if (Schema::hasTable('newstypes') && Schema::hasColumn('newstypes', 'alias'))
		{
			Schema::table('newstypes', function (Blueprint $table)
			{
				$table->dropColumn('alias');
			});
		}
	}
}
