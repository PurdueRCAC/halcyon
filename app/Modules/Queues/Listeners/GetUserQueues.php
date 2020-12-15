<?php

namespace App\Modules\Queues\Listeners;

use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Events\UserDeleted;
use App\Modules\Queues\Models\User as QueueUser;

/**
 * User listener for queues
 */
class GetUserQueues
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
		$events->listen(UserDeleted::class, self::class . '@handleUserDeleted');
	}

	/**
	 * Display user profile info
	 *
	 * @param   object  $event  UserBeforeDisplay
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$user = $event->getUser();

		$memberofqueues = collect([]);
		$pendingqueues = collect([]);
		$priormemberofqueues = collect([]);
		$priorpendingmemberofqueues = collect([]);

		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			//->whereIsMember()
			//->whereIsActive()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.users.read', ['id' => $membership->id]);
			$membership->queueapi = route('api.queues.read', ['id' => $membership->queueid]);

			$queue = $membership->queue()->withTrashed()->first();
			$scheduler = $queue->scheduler()->withTrashed()->first();

			if (!$queue->groupid)
			{
				$groupqueueusers = $membership->groupUser()
					->withTrashed()
					->get();

				foreach ($groupqueueusers as $groupqueueuser)
				{
					$membership->groupid = $groupqueueuser->groupid;
					$membership->datetimecreated = $groupqueueuser->datetimecreated;
					$membership->datetimeremoved = $groupqueueuser->datetimeremoved;

					if (!$membership->isTrashed()
					&& !$queue->isTrashed()
					&& !$scheduler->isTrashed())
					{
						if ($membership->isMember() || $membership->isManager())
						{
							$memberofqueues->add($membership);
						}
						elseif ($membership->isPending())
						{
							$pendingqueues->add($membership);
						}
					}
					else
					{
						if (!$membership->isTrashed())
						{
							if ($queue->isTrashed())
							{
								$membership->datetimeremoved = $queue->datetimeremoved;
							}
							elseif ($scheduler->isTrashed())
							{
								$membership->datetimeremoved = $scheduler->datetimeremoved;
							}
						}

						if ($membership->isMember() || $membership->isManager())
						{
							$priormemberofqueues->add($membership);
						}
						elseif ($membership->isPending())
						{
							$priorpendingmemberofqueues->add($membership);
						}
					}
				}

				continue;
			}

			if (!$membership->isTrashed()
			 && !$queue->isTrashed()
			 && !$scheduler->isTrashed())
			{
				if ($membership->isMember() || $membership->isManager())
				{
					$memberofqueues->add($membership);
				}
				elseif ($membership->isPending())
				{
					$pendingqueues->add($membership);
				}
			}
			else
			{
				if (!$membership->isTrashed())
				{
					if ($queue->isTrashed())
					{
						$membership->datetimeremoved = $queue->datetimeremoved;
					}
					elseif ($scheduler->isTrashed())
					{
						$membership->datetimeremoved = $scheduler->datetimeremoved;
					}
				}

				if ($membership->isMember() || $membership->isManager())
				{
					$priormemberofqueues->add($membership);
				}
				elseif ($membership->isPending())
				{
					$priorpendingmemberofqueues->add($membership);
				}
			}
		}

		// Member
		$user->memberofqueues = $memberofqueues;

		// Pending member
		$user->pendingmemberofqueues = $pendingqueues;

		// Prior member
		$user->priormemberofqueues = $priormemberofqueues;

		// Prior pending member
		$user->priorpendingmemberofqueues = $priorpendingmemberofqueues;

		// Viewers
		/*$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->whereIsActive()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.read', ['id' => $membership->queueid]);
		}

		$user->viewerofgroups = $memberships;

		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->whereIsTrashed()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.read', ['id' => $membership->queueid]);
		}

		$user->priorviewerofgroups = $memberships;*/

		$event->setUser($user);
	}

	/**
	 * Mark membership as removed when a user is deleted
	 *
	 * @param   object  $event  UserDeleted
	 * @return  void
	 */
	public function handleUserDeleted(UserDeleted $event)
	{
		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $event->user->id)
			->whereIsActive()
			->get();

		foreach ($memberships as $membership)
		{
			$membership->delete();
		}
	}
}
