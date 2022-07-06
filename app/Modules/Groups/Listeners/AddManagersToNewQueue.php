<?php

namespace App\Modules\Groups\Listeners;

use App\Modules\Queues\Events\QueueCreated;

/**
 * Queue listener
 */
class AddManagersToNewQueue
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(QueueCreated::class, self::class . '@handleQueueCreated');
	}

	/**
	 * Auto-add group managers to the queue
	 *
	 * @param   QueueCreated $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event)
	{
		$queue = $event->queue;

		if (!$queue || !$queue->group || !$queue->group->cascademanagers)
		{
			return;
		}

		foreach ($queue->group->managers as $manager)
		{
			$queue->addUser($manager->userid);
		}
	}
}
