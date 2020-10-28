<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Groups\Storage;

use App\Modules\Groups\Events\GroupDisplay;

/**
 * Storage listener for group events
 */
class Storage
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

		$content = view('storage::' . $client . '.directories.group', [
			'group' => $group
		]);

		$event->addSection(
			'storage',
			trans('storage::storage.storage'),
			($event->getActive() == 'storage'),
			$content
		);
	}
}
