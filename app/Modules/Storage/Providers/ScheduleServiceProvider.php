<?php

namespace App\Modules\Storage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Storage\Console\EmailQuotaCommand;
use App\Modules\Storage\Console\QuotaCheckCommand;
use App\Modules\Storage\Console\QuotaUpdateCommand;

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

			$cron = config('module.storage.schedule.quotacheck', '*/30 * * * *');
			if ($cron)
			{
				$schedule->command(QuotaCheckCommand::class)->cron($cron);
			}

			$cron = config('module.storage.schedule.emailquota', '*/30 * * * *');
			if ($cron)
			{
				$schedule->command(EmailQuotaCommand::class)->cron($cron);
			}

			$cron = config('module.storage.schedule.quotaupdate', '0 4 * * *');
			if ($cron)
			{
				$schedule->command(QuotaUpdateCommand::class)->cron($cron);
			}
		});
	}
}
