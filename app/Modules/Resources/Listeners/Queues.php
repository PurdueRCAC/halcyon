<?php

namespace App\Modules\Resources\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\UserDeleted as QueueUserDeleted;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Groups\Models\Member as GroupMember;
use App\Modules\Groups\Models\Type;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberDeleted;

/**
 * Queue listener
 */
class Queues
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
		$events->listen(QueueUserDeleted::class, self::class . '@handleQueueUserDeleted');
	}

	/**
	 * Auto-add group managers to any of the group's queues/resources
	 *
	 * @param   QueueCreated $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event): void
	{
		$queue = $event->queue;

		if (!$queue->group || !$queue->group->cascademanagers)
		{
			return;
		}

		// Create roles as necessary
		if ($queue->scheduler
		 && $queue->scheduler->resource
		 && $queue->scheduler->resource->rolename)
		{
			foreach ($queue->group->managers as $user)
			{
				event($resourcemember = new ResourceMemberStatus($queue->scheduler->resource, $user->user));

				if ($resourcemember->status <= 0)
				{
					throw new \Exception(__METHOD__ . '(): Bad status for `resourcemember` ' . $user->userid . '.' . $queue->scheduler->resource->id);
				}

				if ($resourcemember->noStatus()
				 || $resourcemember->isPendingRemoval())
				{
					event($resourcemember = new ResourceMemberCreated($queue->scheduler->resource, $user->user));
				}
			}
		}
	}

	/**
	 * Check if a user should still have access to a resource and handle accordingly
	 *
	 * @param   QueueUserDeleted $event
	 * @return  void
	 */
	public function handleQueueUserDeleted(QueueUserDeleted $event): void
	{
		/** @var QueueUser $row */
		$row = $event->user;

		if (!$row->queue || !$row->queue->scheduler || !$row->queue->scheduler->resource)
		{
			return;
		}

		// Check the user's status for the resource
		event($resourcemember = new ResourceMemberStatus($row->queue->scheduler->resource, $row->user));

		// If the user has access to the resource,
		// count valid queues that allows them access.
		if ($resourcemember->isPending() || $resourcemember->isReady())
		{
			$rows = 0;

			$subresources = $row->queue->scheduler->resource->subresources;

			if ($subresources)
			{
				$m = (new QueueUser)->getTable();
				$q = (new Queue)->getTable();

				foreach ($subresources as $sub)
				{
					$rows += QueueUser::query()
						->join($q, $q . '.id', $m . '.queueid')
						->where($q . '.subresourceid', '=', $sub->id)
						->where($m . '.userid', '=', $row->userid)
						->count();

					// Get group IDs where the user is a manager
					$groupids = GroupMember::query()
						->select('groupid')
						->where('membertype', '=', Type::MANAGER)
						->where('userid', '=', $row->userid)
						->get()
						->pluck('groupid')
						->toArray();

					if (count($groupids))
					{
						// Find the queues belonging to the groups
						//
						// NOTE: We do this because there might be cases where a user is a manager
						//       of a group but does not have an explicit queue_user entry.
						//       Managers automatically have access to all group queues.
						$rows += Queue::query()
							->whereIn('groupid', $groupids)
							->where('subresourceid', '=', $sub->id)
							->count();
					}

					/*$queues = $sub->queues()
						//->whereIn('groupid', $owned)
						->get();
						//->pluck('queuid')
						//->toArray();

					foreach ($queues as $queue)
					{
						$rows += $queue->users()
							->whereIsMember()
							->where('userid', '=', $row->userid)
							->count();

						if ($queue->group)
						{
							$rows += $queue->group->members()
								->whereIsManager()
								->where('userid', '=', $row->userid)
								->count();
						}
					}*/
				}
			}

			if ($rows == 0)
			{
				// No other active memberships found, remove resource access
				event(new ResourceMemberDeleted($row->queue->scheduler->resource, $row->user));
			}
		}
	}
}
