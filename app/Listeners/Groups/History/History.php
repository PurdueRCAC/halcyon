<?php
namespace App\Listeners\Groups\History;

use App\Modules\Groups\Events\GroupDisplay;

/**
 * History listener for group events
 */
class History
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(GroupDisplay::class, self::class . '@handleGroupDisplay');
	}

	/**
	 * Load history data when displaying a Group
	 *
	 * @param   GroupDisplay  $event
	 * @return  void
	 */
	public function handleGroupDisplay(GroupDisplay $event)
	{
		$content = null;
		$group = $event->getGroup();
		$client = app('isAdmin') ? 'admin' : 'site';

		$content = view('groups::site.group.history', [
			'group' => $group
		]);

		$event->addSection(
			'history',
			trans('groups::groups.history.title'),
			($event->getActive() == 'history'),
			$content
		);
	}
}
