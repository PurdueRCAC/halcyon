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
			$schedules = config('module.history.schedule', []);

			if (!empty($schedules))
			{
				$scheduler = $this->app->make(Schedule::class);

				foreach ($schedules as $command => $schedule)
				{
					$scheduler->command($command)->cron($schedule);
				}
			}
		});
	}
}
