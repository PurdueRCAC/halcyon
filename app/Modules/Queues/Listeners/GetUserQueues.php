<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

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

		// Member
		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->whereIsActive()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.read', ['id' => $membership->queueid]);
		}

		$user->memberofqueues = $memberships;

		// Pending member
		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsPending()
			->whereIsActive()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.read', ['id' => $membership->queueid]);
		}

		$user->pendingmemberofqueues = $memberships;

		// Prior member
		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->whereIsTrashed()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.read', ['id' => $membership->queueid]);
		}

		$user->priormemberofqueues = $memberships;

		// Prior pending member
		$memberships = QueueUser::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsPending()
			->whereIsTrashed()
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.queues.read', ['id' => $membership->queueid]);
		}

		$user->priorpendingmemberofqueues = $memberships;

		// Viewers
		$memberships = QueueUser::query()
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

		$user->priorviewerofgroups = $memberships;

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
