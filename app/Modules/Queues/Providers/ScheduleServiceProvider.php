<?php

namespace App\Modules\Queues\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$commands = config('module.queues.schedule', []);

			foreach ($commands as $command => $cron)
			{
				if (!$cron)
				{
					continue;
				}

				$schedule->command('queues:' . $command)->cron($cron);
			}
		});
	}
}
