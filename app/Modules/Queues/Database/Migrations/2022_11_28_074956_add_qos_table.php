<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQosTable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('queueqos'))
		{
			Schema::create('queueqos', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('scheduler_id')->unsigned()->default(0);
				$table->string('name', 255);
				$table->text('description')->nullable();
				$table->string('flags', 500)->nullable();
				$table->integer('grace_time')->unsigned()->nullable();
				$table->integer('max_jobs_pa')->unsigned()->nullable();
				$table->integer('max_jobs_per_user')->unsigned()->nullable();
				$table->integer('max_jobs_accrue_pa')->unsigned()->nullable();
				$table->integer('max_jobs_accrue_pu')->unsigned()->nullable();
				$table->integer('min_prio_thresh')->unsigned()->nullable();
				$table->integer('max_submit_jobs_pa')->unsigned()->nullable();
				$table->integer('max_submit_jobs_per_user')->unsigned()->nullable();
				$table->text('max_tres_pa')->nullable();
				$table->text('max_tres_pj')->nullable();
				$table->text('max_tres_pn')->nullable();
				$table->text('max_tres_pu')->nullable();
				$table->text('max_tres_mins_pj')->nullable();
				$table->text('max_tres_run_mins_pa')->nullable();
				$table->text('max_tres_run_mins_pu')->nullable();
				$table->text('min_tres_pj')->nullable();
				$table->integer('max_wall_duration_per_job')->unsigned()->nullable();
				$table->integer('grp_jobs')->unsigned()->nullable();
				$table->integer('grp_jobs_accrue')->unsigned()->nullable();
				$table->integer('grp_submit_jobs')->unsigned()->nullable();
				$table->text('grp_tres')->nullable();
				$table->text('grp_tres_mins')->nullable();
				$table->text('grp_tres_run_mins')->nullable();
				$table->integer('grp_wall')->unsigned()->nullable();
				$table->text('preempt')->nullable();
				$table->text('preempt_mode')->default(0);
				$table->integer('preempt_exempt_time')->unsigned()->nullable();
				$table->integer('priority')->unsigned()->default(0);
				$table->float('usage_factor', 9, 4)->default(0.00);
				$table->float('usage_thres', 9, 4)->default(0.00);
				$table->float('limit_factor', 9, 4)->default(0.00);
				$table->dateTime('datetimecreated')->nullable();
				$table->dateTime('datetimeedited')->nullable();
				$table->dateTime('datetimeremoved')->nullable();
				$table->unique('name');
			});
		}

		if (!Schema::hasTable('queueqoses'))
		{
			Schema::create('queueqoses', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('qosid')->unsigned()->default(0)->comment('FK to queueqos.id');
				$table->integer('queueid')->unsigned()->default(0)->comment('FK to queues.id');
				$table->index('qosid');
				$table->index('queueid');
				$table->index('qosqueue', ['qosid', 'queueid']);
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
			'queueqos',
			'queueqoses',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);
		}
	}
}
