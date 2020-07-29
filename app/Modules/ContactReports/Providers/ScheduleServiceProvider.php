<?php

namespace App\Modules\ContactReports\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Orders\Console\EmailReportsCommand;
use App\Modules\Orders\Console\EmailCommentsCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailReportsCommand::class)->cron(config('module.contactreports.schedule.emailreports', '*/10 * * * *'));

			$schedule->command(EmailCommentsCommand::class)->cron(config('module.contactreports.schedule.emailcomments', '*/10 * * * *'));
		});
	}
}
