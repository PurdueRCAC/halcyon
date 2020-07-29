<?php

namespace App\Modules\Orders\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);
			//$schedule->command('module:orders renew')->dailyAt('23:55');
			$schedule->command('orders:renew')->cron(config('module.orders.schedule.renew', '55 23 * * *'));
			//$schedule->command('module:orders process')->everyFifteenMinutes();
			$schedule->command('orders:emailstatus')->cron(config('module.orders.schedule.emailstatus', '*/15 * * * *'));
		});
	}
}
