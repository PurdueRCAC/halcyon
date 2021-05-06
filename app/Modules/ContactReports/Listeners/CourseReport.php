<?php

namespace App\Modules\ContactReports\Listeners;

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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AccountCreated::class, self::class . '@handleAccountCreated');
	}

	/**
	 * Handle a Course account being created
	 *
	 * @param   object   $event  AccountCreated
	 * @return  void
	 */
	public function handleAccountCreated(AccountCreated $event)
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
