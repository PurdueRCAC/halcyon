<?php

namespace App\Modules\Storage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
	/**
	 * Set up scheduled commands
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$commands = config('module.storage.schedule', []);

			foreach ($commands as $command => $cron)
			{
				if (!$cron)
				{
					continue;
				}

				$schedule->command('storage:' . $command)->cron($cron);
			}
		});
	}
}
