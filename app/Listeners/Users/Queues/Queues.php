<?php
namespace App\Listeners\Users\Queues;

use App\Modules\Users\Events\UserDisplay;

/**
 * User listener for queues
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
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

	/**
	 * Display session data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		if (!auth()->user())
		{
			return;
		}

		$user = $event->getUser();

		// Owner groups
		$memberships = $user->groups()
			->where('groupid', '>', 0)
			->whereIsManager()
			->get();

		$ids = array();
		$allqueues = array();
		foreach ($memberships as $membership)
		{
			$group = $membership->group;

			$queues = $group->queues;

			foreach ($queues as $queue)
			{
				$ids[] = $queue->id;

				if (!$queue || $queue->trashed())
				{
					continue;
				}

				if (!$queue->scheduler || $queue->scheduler->trashed())
				{
					continue;
				}

				$queue->status = 'member';

				$allqueues[] = $queue;
			}
		}

		$queues = $user->queues()
			->whereNotIn('queueid', $ids)
			->get();

		foreach ($queues as $qu)
		{
			if ($qu->trashed())
			{
				continue;
			}

			$queue = $qu->queue;

			if (!$queue || $queue->trashed())
			{
				continue;
			}

			if (!$queue->scheduler || $queue->scheduler->trashed())
			{
				continue;
			}

			$group = $queue->group;

			if (!$group || !$group->id)
			{
				continue;
			}

			if ($qu->isPending())
			{
				$queue->status = 'pending';
			}
			else
			{
				$queue->status = 'member';
			}

			$allqueues[] = $queue;
		}

		/*app('translator')->addNamespace(
			'listener.users.queues',
			__DIR__ . '/lang'
		);*/

		$content = view('queues::site.profile', [
			'user' => $user,
			'queues' => $allqueues,
		]);

		$event->addPart(
			$content
		);
	}
}
