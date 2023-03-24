<?php

namespace App\Modules\Resources\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Resources\Console\EmailSchedulingCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	/**
	 * Set up scheduled tasks
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailSchedulingCommand::class)->cron(config('module.resources.schedule.emailscheduling', '*/5 * * * *'));
		});
	}
}
