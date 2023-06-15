<?php

namespace App\Modules\ContactReports\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Groups\Events\GroupReading;
use App\Modules\ContactReports\Models\Report;

/**
 * Group listener
 */
class GroupReports
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(GroupReading::class, self::class . '@handleGroupReading');
	}

	/**
	 * Get a list of reports for this group
	 *
	 * @param   GroupReading $event
	 * @return  void
	 */
	public function handleGroupReading(GroupReading $event): void
	{
		$event->group->contactreports = Report::query()
			->where('groupid', '=', $event->group->id)
			->get();
	}
}
