<?php

namespace App\Modules\Resources\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Resources\Console\EmailSchedulingCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailSchedulingCommand::class)->cron(config('module.resources.schedule.emailscheduling', '*/5 * * * *'));
		});
	}
}
