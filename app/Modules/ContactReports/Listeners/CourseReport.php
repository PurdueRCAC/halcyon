<?php

namespace App\Modules\ContactReports\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Courses\Events\AccountCreated;
use App\Modules\ContactReports\Models\Report;
use Carbon\Carbon;

/**
 * Courses listener
 */
class CourseReport
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AccountCreated::class, self::class . '@handleAccountCreated');
	}

	/**
	 * Handle a Course account being created
	 *
	 * @param   AccountCreated  $event
	 * @return  void
	 */
	public function handleAccountCreated(AccountCreated $event): void
	{
		// Does the course have a report?
		if (!$event->account->report)
		{
			return;
		}

		$report = new Report;
		$report->report = $event->account->report;
		$report->userid = auth()->user()->id;
		$report->contactdate = Carbon::now()->toDateTimeString();
		$report->save();
	}
}
