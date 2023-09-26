<?php

namespace App\Modules\Queues\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserDeleted;
use App\Modules\Queues\Models\User;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\Queue;
use App\Modules\Groups\Events\MemberDeleted;

/**
 * Queue listener to remove memberships when a user is removed
 */
class RemoveMembershipsForDeletedUser
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param   Dispatcher  $events
	 * @return  void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UserDeleted::class, self::class . '@handleUserDeleted');
		$events->listen(MemberDeleted::class, self::class . '@handleMemberDeleted');
	}

	/**
	 * Handle an updated User Request
	 *
	 * @param   UserDeleted  $event
	 * @return  void
	 */
	public function handleUserDeleted(UserDeleted $event): void
	{
		if (!$event->user || !$event->user->id)
		{
			return;
		}

		User::query()
			->where('userid', '=', $event->user->id)
			->delete();

		GroupUser::query()
			->where('userid', '=', $event->user->id)
			->delete();
	}

	/**
	 * Handle removal from a group
	 *
	 * @param   MemberDeleted  $event
	 * @return  void
	 */
	public function handleMemberDeleted(MemberDeleted $event): void
	{
		if (!$event->member->group)
		{
			return;
		}

		$queues = Queue::query()
			->where('groupid', '=', $event->member->groupid)
			->pluck('id')
			->toArray();

		if (count($queues) > 0)
		{
			return;
		}

		$queueusers = User::query()
			->where('userid', '=', $event->member->userid)
			->whereIn('queueid', $queues)
			->get()
			->pluck('id')
			->toArray();
	
		User::query()
			->where('userid', '=', $event->member->userid)
			->whereIn('queueid', $queues)
			->delete();

		if (count($queueusers) > 0)
		{
			GroupUser::query()
				->whereIn('queueuserid', $queueusers)
				->delete();
		}
	}
}
