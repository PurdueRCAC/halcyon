<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('groups'))
		{
			Schema::create('groups', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 48);
				$table->integer('owneruserid')->unsigned()->default(0);
				$table->string('unixgroup', 48);
				$table->integer('unixid')->unsigned()->default(0);
				$table->integer('deptnumber')->unsigned()->default(0);
				$table->integer('onepurdue')->unsigned()->default(0);
				$table->string('githuborgname', 39);
				$table->index('unixgroup');
				$table->index('unixid');
				$table->index('owneruserid');
			});
			//$this->info('Created `groups` table.');
		}

		if (!Schema::hasTable('groupusers'))
		{
			Schema::create('groupusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('userrequestid')->unsigned()->default(0);
				$table->integer('membertype')->unsigned()->default(0);
				$table->integer('owner')->unsigned()->default(0);
				$table->timestamp('created_at');
				$table->timestamp('deleted_at');
				$table->timestamp('last_visit');
				$table->integer('notice')->unsigned()->default(0);
				$table->index(['groupid','userid','datecreated','dateremoved']);
				$table->index(['groupid','datecreated','dateremoved']);
				$table->index(['userid','membertype','datecreated','dateremoved']);
				$table->index(['groupid','userid','membertype','datecreated','dateremoved']);
				$table->index(['groupid','membertype','datecreated','dateremoved']);
				$table->index('datelastseen');
				$table->index('userrequestid');
				$table->index(['notice','groupid']);
				$table->index(['groupid','membertype','owner','datecreated','dateremoved']);
			});
			//$this->info('Created `groupusers` table.');
		}

		if (!Schema::hasTable('groupmotds'))
		{
			Schema::create('groupmotds', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0);
				$table->text('motd');
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->index('groupid');
			});
			//$this->info('Created `groupmotds` table.');
		}

		if (!Schema::hasTable('groupcollegedept'))
		{
			Schema::create('groupcollegedept', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0);
				$table->integer('collegedeptid')->unsigned()->default(0);
				$table->integer('percentage')->unsigned()->default(0);
				$table->index('groupid');
				$table->index('collegedeptid');
			});
			//$this->info('Created `groupcollegedept` table.');
		}

		if (!Schema::hasTable('groupfieldofscience'))
		{
			Schema::create('groupfieldofscience', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0);
				$table->integer('fieldofscienceid')->unsigned()->default(0);
				$table->integer('newid')->unsigned()->default(0);
				$table->integer('percentage')->unsigned()->default(0);
				$table->index('groupid');
				$table->index(['fieldofscienceid', 'groupid']);
			});
			//$this->info('Created `groupfieldofscience` table.');
		}

		if (!Schema::hasTable('unixgroups'))
		{
			Schema::create('unixgroups', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0);
				$table->mediumInteger('unixgid')->unsigned()->default(0);
				$table->string('shortname', 8);
				$table->string('longname', 32);
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->index(['groupid', 'datetimecreated', 'datetimeremoved']);
				$table->index(['longname', 'datetimecreated', 'datetimeremoved']);
			});
			//$this->info('Created `unixgroups` table.');
		}

		if (!Schema::hasTable('unixgroupusers'))
		{
			Schema::create('unixgroupusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('unixgroupid')->unsigned()->default(0);
				$table->integer('userid')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->timestamp('datetimeremoved');
				$table->timestamp('last_visit');
				$table->integer('notice')->unsigned()->default(0);
				$table->index(['unixgroupid', 'datetimecreated', 'datetimeremoved']);
				$table->index(['userid', 'datetimecreated', 'datetimeremoved']);
				$table->index(['notice', 'unixgroupid']);
			});
			//$this->info('Created `unixgroupusers` table.');
		}

		if (!Schema::hasTable('membertypes'))
		{
			Schema::create('membertypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name');
			});
			//$this->info('Created `membertypes` table.');

			// Populate defaults
			$membertypes = array(
				'group member',
				'group manager',
				'group usage viewer',
				'request pending',
				'unix group',
				'Radon',
				'BoilerGrid',
				'Fortress',
				'contact manager',
				'contact follower',
				'news editor',
				'order manager',
				'order member',
				'queue manager',
				'group admin',
				'storage manager',
				'class admin',
			);

			foreach ($membertypes as $type)
			{
				DB::table('membertypes')->insert([
					'name' => $type,
				]);
			}
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
			'groups',
			'groupusers',
			'groupmotds',
			'groupcollegedept',
			'groupfieldofscience',
			'unixgroups',
			'unixgroupusers',
			'membertypes',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);

			//$this->info('Dropped `' . $table . '` table.');
		}
	}
}
