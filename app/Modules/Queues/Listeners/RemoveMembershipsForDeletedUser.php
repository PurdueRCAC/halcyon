<?php

namespace App\Modules\Queues\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserDeleted;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\Queue;
use App\Modules\Groups\Events\MemberDeleted;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberDeleted;

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
	 * Handle a user account being deleted
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

		QueueUser::query()
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

		// Get all queues that belong to the same group
		$queues = Queue::query()
			->where('groupid', '=', $event->member->groupid)
			->pluck('id')
			->toArray();

		if (count($queues) == 0)
		{
			return;
		}

		// Delete group queue user entries
		$queueusers = QueueUser::query()
			->where('userid', '=', $event->member->userid)
			->whereIn('queueid', $queues)
			->get()
			->pluck('id')
			->toArray();

		if (count($queueusers) > 0)
		{
			GroupUser::query()
				->whereIn('queueuserid', $queueusers)
				->delete();
		}

		// Delete queue user entries
		//
		// NOTE: We do this in a loop rather than the batch delete
		//       because we need each entry to trigger its 'deleted'
		//       event. Other modules may be listening to perform
		//       clean up operations.

		/* Batch deletion
		QueueUser::query()
			->where('userid', '=', $event->member->userid)
			->whereIn('queueid', $queues)
			->delete();*/

		$queueusers = QueueUser::query()
			->where('userid', '=', $event->member->userid)
			->whereIn('queueid', $queues)
			->get();

		foreach ($queueusers as $queueuser)
		{
			$queueuser->delete();
		}
	}
}
