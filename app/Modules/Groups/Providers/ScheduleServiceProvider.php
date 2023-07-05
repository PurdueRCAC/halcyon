<?php

namespace App\Modules\Groups\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

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

			$commands = config('module.groups.schedule', []);

			foreach ($commands as $command => $cron)
			{
				if (!$cron)
				{
					continue;
				}

				$schedule->command('groups:' . $command)->cron($cron);
			}
		});
	}
}
