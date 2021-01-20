<?php

namespace App\Modules\Status\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);
			$schedule->command('status:renew')->cron(config('module.status.schedule.fetch', '0 * * * *'));
		});
	}
}
