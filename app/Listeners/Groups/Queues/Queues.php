<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
	 * Plugin that loads module positions within content
	 *
	 * @param   GroupDisplay $event
	 * @return  void
	 */
	public function handleGroupDisplay(GroupDisplay $event)
	{
		$content = null;
		$group = $event->getGroup();
		$client = app('isAdmin') ? 'admin' : 'site';

		$content = view('queues::site.group', [
			'group' => $group
		]);

		$event->addSection(
			'queues',
			trans('queues::queues.queues'),
			($event->getActive() == 'queues'),
			$content
		);
	}
}
