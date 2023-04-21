<?php

namespace App\Modules\News\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedules = config('module.news.schedule', []);

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
