<?php

namespace App\Modules\Resources\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

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

			$commands = config('module.resources.schedule', []);

			foreach ($commands as $command => $cron)
			{
				if (!$cron)
				{
					continue;
				}

				$schedule->command('resources:' . $command)->cron($cron);
			}
		});
	}
}
