<?php

namespace App\Modules\Courses\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Courses\Console\EmailAdditionsCommand;
use App\Modules\Courses\Console\EmailRemovalsCommand;
use App\Modules\Courses\Console\SyncCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailAdditionsCommand::class)->cron(config('module.courses.schedule.emailadditions', '*/20 * * * *'));

			$schedule->command(EmailRemovalsCommand::class)->cron(config('module.courses.schedule.emailremovals', '*/20 * * * *'));

			$schedule->command(SyncCommand::class)->cron(config('module.courses.schedule.sync', '5 0 * * *'));
			//$schedule->command(SyncCommand::class)->cron(config('module.courses.schedule.sync', '0 8 * * *'));
		});
	}
}
