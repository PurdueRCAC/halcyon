<?php

namespace App\Modules\Groups\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Queues\Events\QueueCreated;

/**
 * Queue listener
 */
class AddManagersToNewQueue
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(QueueCreated::class, self::class . '@handleQueueCreated');
	}

	/**
	 * Auto-add group managers to the queue
	 *
	 * @param   QueueCreated $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event): void
	{
		$queue = $event->queue;

		if (!$queue || !$queue->group || !$queue->group->cascademanagers)
		{
			return;
		}

		if ($queue->group->unixgroup && substr($queue->group->unixgroup, 0, 2) == 'x-')
		{
			return;
		}

		foreach ($queue->group->managers as $manager)
		{
			if (!$manager->user || substr($manager->user->username, 0, 2) == 'x-')
			{
				continue;
			}
			$queue->addUser($manager->userid);
		}
	}
}
