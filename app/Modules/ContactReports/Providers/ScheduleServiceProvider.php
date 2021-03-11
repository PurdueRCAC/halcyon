<?php

namespace App\Modules\ContactReports\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Orders\Console\EmailReportsCommand;
use App\Modules\Orders\Console\EmailCommentsCommand;
use App\Modules\Orders\Console\EmailFollowupsCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailReportsCommand::class)->cron(config('module.contactreports.schedule.emailreports', '*/10 * * * *'));

			$schedule->command(EmailCommentsCommand::class)->cron(config('module.contactreports.schedule.emailcomments', '*/10 * * * *'));

			$schedule->command(EmailFollowupsCommand::class)->cron(config('module.contactreports.schedule.emailfollowups', '0 10 * * 1-5'));
		});
	}
}
