<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Groups\Models\Group;

class AddGroupTimestamps extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		// Resources
		if (Schema::hasTable('groups'))
		{
			if (!Schema::hasColumn('groups', 'datetimecreated'))
			{
				// ALTER TABLE `groups` ADD COLUMN `datetimecreated` DATETIME;
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dateTime('datetimecreated')->nullable();
				});

				$groups = Group::query()->orderBy('id', 'asc')->get();

				foreach ($groups as $group)
				{
					$first = $group->members()
						->whereNotNull('datecreated')
						->orderBy('id', 'asc')
						->limit(1)
						->get()
						->first();

					if (!$first)
					{
						continue;
					}

					$group->update(['datetimecreated' => $first->datecreated]);
				}
			}

			if (!Schema::hasColumn('groups', 'datetimeremoved'))
			{
				// ALTER TABLE `groups` ADD COLUMN `datetimeremoved` DATETIME;
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dateTime('datetimeremoved')->nullable();
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
		if (Schema::hasTable('groups'))
		{
			if (Schema::hasColumn('groups', 'datetimecreated'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('datetimecreated');
				});
			}

			if (Schema::hasColumn('groups', 'datetimeremoved'))
			{
				Schema::table('groups', function (Blueprint $table)
				{
					$table->dropColumn('datetimeremoved');
				});
			}
		}
	}
}
