<?php
namespace App\Listeners\Groups\Queues;

use App\Modules\Groups\Events\GroupDisplay;

/**
 * Queues listener for group events
 */
class Queues
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
	 * Load Queues data when displaying a Group
	 *
	 * @param   GroupDisplay  $event
	 * @return  void
	 */
	public function handleGroupDisplay(GroupDisplay $event)
	{
		$content = null;
		$group = $event->getGroup();
		$client = app('isAdmin') ? 'admin' : 'site';

		if ($event->getActive() == 'queues' || $client == 'admin')
		{
			$content = view('queues::site.group', [
				'group' => $group
			]);
		}

		$event->addSection(
			'queues',
			trans('queues::queues.queues'),
			($event->getActive() == 'queues'),
			$content
		);
	}
}
