<?php

namespace App\Modules\Resources\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserUpdated;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;

/**
 * Users listener
 */
class Users
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UserUpdated::class, self::class . '@handleUserUpdated');
	}

	/**
	 * Add/remove memberships based on account suspension status
	 *
	 * @param   UserUpdated  $event
	 * @return  void
	 */
	public function handleUserUpdated(UserUpdated $event): void
	{
		$user = $event->user;

		// Only respond if the enabled attribute changed
		if ($user->enabled == $user->getOriginal('enabled'))
		{
			return;
		}

		$queues = $user->queues()
			->get();

		// Collect only active memberships
		$subresources = array();

		foreach ($queues as $qu)
		{
			$queue = $qu->queue;

			if (!$queue || $queue->trashed())
			{
				continue;
			}

			if (!$queue->scheduler || $queue->scheduler->trashed())
			{
				continue;
			}

			if (!$queue->subresource || !$queue->subresource->id)
			{
				continue;
			}

			$subresources[] = $queue->subresourceid;
		}

		$subresources = array_unique($subresources);

		if (empty($subresources))
		{
			return;
		}

		$a = (new Asset)->getTable();
		$s = (new Child)->getTable();

		$resources = Asset::query()
			->select($a . '.id', $a . '.rolename')
			->join($s, $s . '.resourceid', $a . '.id')
			->whereIn($s . '.subresourceid', $subresources)
			->groupBy($a . '.id')
			->groupBy($a . '.rolename')
			->get();

		foreach ($resources as $resource)
		{
			if ($user->enabled && !$user->getOriginal('enabled'))
			{
				event($resourcemember = new ResourceMemberCreated($resource, $user));
			}
			elseif (!$user->enabled && $user->getOriginal('enabled'))
			{
				event($resourcemember = new ResourceMemberDeleted($resource, $user));
			}
		}
	}
}
