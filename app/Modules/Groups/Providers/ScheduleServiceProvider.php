<?php

namespace App\Modules\Groups\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Groups\Console\EmailAuthorizedCommand;
use App\Modules\Groups\Console\EmailRemovedCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	/**
	 * Boot scheduled events.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailAuthorizedCommand::class)->cron((string)config('module.groups.schedule.emailauthorized', '*/20 * * * *'));

			$schedule->command(EmailRemovedCommand::class)->cron((string)config('module.groups.schedule.emailremoved', '*/20 * * * *'));
		});
	}
}
