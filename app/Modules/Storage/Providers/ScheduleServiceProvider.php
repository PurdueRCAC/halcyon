<?php

namespace App\Modules\Storage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Storage\Console\EmailQuotaCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailQuotaCommand::class)->cron(config('module.storage.schedule.emailquota', '*/30 * * * *'));
		});
	}
}
