<?php

namespace App\Modules\History\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
	/**
	 * Register scheduled commands
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command('log:purge')->cron(config('module.history.schedule.purge_log', '55 23 * * *'));

			//$schedule->command('history:purge')->cron(config('module.history.schedule.purge_history', '55 23 * * *'));
		});
	}
}
