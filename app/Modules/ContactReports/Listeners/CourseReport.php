<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
	 * Plugin that loads module positions within content
	 *
	 * @param   object   $event  AccountCreated
	 * @return  void
	 */
	public function handleAccountCreated(AccountCreated $event)
	{
		if (!$event->course->report)
		{
			return;
		}

		$report = new Report;
		$report->report = $event->course->report;
		$report->userid = auth()->user()->id;
		$report->contactdate = Carbon::now()->toDateTimeString();
		$report->save();
	}
}
