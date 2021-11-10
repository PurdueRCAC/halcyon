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
				$table->integer('owneruserid')->unsigned()->default(0)->comment('FK to users.id');
				$table->string('unixgroup', 48);
				$table->integer('unixid')->unsigned()->default(0);
				$table->integer('deptnumber')->unsigned()->default(0)->comment('FK to collegedept.id');
				//$table->integer('onepurdue')->unsigned()->default(0);
				$table->string('githuborgname', 39);
				$table->index('unixgroup');
				$table->index('unixid');
				$table->index('owneruserid');
			});
		}

		if (!Schema::hasTable('groupusers'))
		{
			Schema::create('groupusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->integer('userrequestid')->unsigned()->default(0)->comment('FK to userrequests.id');
				$table->integer('membertype')->unsigned()->default(0)->comment('FK to membertypes.id');
				$table->tinyInteger('owner')->unsigned()->default(0);
				$table->dateTime('datecreated')->nullable();
				$table->dateTime('dateremoved')->nullable();
				$table->dateTime('datelastseen')->nullable();
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index(['groupid','userid','datecreated','dateremoved'], 'groupid_1');
				$table->index(['groupid','datecreated','dateremoved'], 'groupid_2');
				$table->index(['userid','membertype','datecreated','dateremoved'], 'userid');
				$table->index(['groupid','userid','membertype','datecreated','dateremoved'], 'groupid_3');
				$table->index(['groupid','membertype','datecreated','dateremoved'], 'groupid_4');
				$table->index('datelastseen');
				$table->index('userrequestid');
				$table->index(['notice','groupid'], 'notice');
				$table->index(['groupid','membertype','owner','datecreated','dateremoved'], 'groupid_6');
			});
		}

		if (!Schema::hasTable('groupnodes'))
		{
			Schema::create('groupnodes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('year', 6);
				$table->integer('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('maxnodes')->unsigned()->default(0);
				$table->decimal('parentnodes', 9, 4)->unsigned()->default(0.0000);
				$table->index(['year', 'resourceid', 'groupid'], 'year');
				$table->index(['resourceid', 'groupid'], 'resourceid');
				$table->index(['groupid', 'year'], 'groupid');
			});
		}

		if (!Schema::hasTable('groupmotds'))
		{
			Schema::create('groupmotds', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->string('motd', 8096);
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->index('groupid');
			});
		}

		if (!Schema::hasTable('groupcollegedept'))
		{
			Schema::create('groupcollegedept', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('collegedeptid')->unsigned()->default(0)->comment('FK to collegedept.id');
				$table->integer('percentage')->unsigned()->default(0);
				$table->index('groupid');
				$table->index('collegedeptid');
			});
		}

		if (!Schema::hasTable('groupfieldofscience'))
		{
			Schema::create('groupfieldofscience', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('fieldofscienceid')->unsigned()->default(0)->comment('FK to fieldofscience.id');
				$table->integer('newid')->unsigned()->default(0);
				$table->integer('percentage')->unsigned()->default(0);
				$table->index('groupid');
				$table->index(['fieldofscienceid', 'groupid'], 'fieldofscienceid');
			});
		}

		if (!Schema::hasTable('groupqueueusers'))
		{
			Schema::create('groupqueueusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->integer('queueuserid')->unsigned()->default(0)->comment('FK to queueusers.id');
				$table->integer('userrequestid')->unsigned()->default(0)->comment('FK to userrequests.id');
				$table->integer('membertype')->unsigned()->default(0)->comment('FK to membertypes.id');
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index(['groupid', 'datetimecreated', 'datetimeremoved'], 'groupid');
				$table->index(['queueuserid', 'datetimecreated', 'datetimeremoved'], 'queueuserid');
				$table->index(['notice', 'groupid'], 'notice');
			});
		}

		if (!Schema::hasTable('unixgroups'))
		{
			Schema::create('unixgroups', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('groupid')->unsigned()->default(0)->comment('FK to groups.id');
				$table->mediumInteger('unixgid')->unsigned()->default(0);
				$table->string('shortname', 8);
				$table->string('longname', 32);
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->index(['groupid', 'datetimecreated', 'datetimeremoved'], 'groupid');
				$table->index(['longname', 'datetimecreated', 'datetimeremoved'], 'longname');
			});
		}

		if (!Schema::hasTable('unixgroupusers'))
		{
			Schema::create('unixgroupusers', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('unixgroupid')->unsigned()->default(0)->comment('FK to unixgroups.id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->integer('notice')->unsigned()->default(0);
				$table->index(['unixgroupid', 'datetimecreated', 'datetimeremoved'], 'unixgroupid');
				$table->index(['userid', 'datetimecreated', 'datetimeremoved'], 'userid');
				$table->index(['notice', 'unixgroupid'], 'notice');
			});
		}

		if (!Schema::hasTable('userrequests'))
		{
			Schema::create('userrequests', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('FK to users.id');
				$table->string('comment', 2048);
				$table->dateTime('datetimecreated')->nullable();
				$table->index(['userid', 'datetimecreated'], 'userid');
			});
		}

		if (!Schema::hasTable('membertypes'))
		{
			Schema::create('membertypes', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name', 20);
			});

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
			'groupnodes',
			'groupcollegedept',
			'groupfieldofscience',
			'groupqueueusers',
			'unixgroups',
			'unixgroupusers',
			'membertypes',
			'userrequests'
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
