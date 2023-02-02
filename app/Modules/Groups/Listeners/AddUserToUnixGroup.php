<?php

namespace App\Modules\Groups\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Queues\Events\UserRequestUpdated;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Groups\Models\UnixGroupMember;

/**
 * Group listener to add a user to a unix group
 */
class AddUserToUnixGroup
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param   Dispatcher  $events
	 * @return  void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UserRequestUpdated::class, self::class . '@handleUserRequestUpdated');
	}

	/**
	 * Handle an updated User Request
	 *
	 * @param   UserRequestUpdated  $event
	 * @return  void
	 */
	public function handleUserRequestUpdated(UserRequestUpdated $event): void
	{
		if (!$event->userrequest)
		{
			return;
		}

		// Ensure the client is authorized to manage the controlling group.
		$u = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$queueusers = QueueUser::query()
			->select($u . '.*', $q . '.groupid')
			->join($q, $q . '.id', $u . '.queueid')
			->where($u . '.userrequestid', '=', $event->userrequest->id)
			//->wherePendingRequest()
			->get();

		if (!count($queueusers))
		{
			$gu = (new GroupUser)->getTable();

			$queueusers = GroupUser::query()
				->select($gu . '.*')
				->join($u, $u . '.id', $gu . '.queueuserid')
				->where($gu . '.userrequestid', '=', $event->userrequest->id)
				//->wherePendingRequest()
				->get();

			if (!count($queueusers))
			{
				return;
			}
		}

		foreach ($queueusers as $queueuser)
		{
			// Need to create membership in base group
			if ($queueuser->group && $queueuser->group->unixgroup)
			{
				// Get base unix group
				$base = $queueuser->group->primaryUnixGroup;

				if (!$base)
				{
					return;
				}

				// Look for user's membership
				$baserow = UnixGroupMember::query()
					->withTrashed()
					->where('unixgroupid', '=', $base->id)
					->where('userid', '=', $event->userrequest->userid)
					->get()
					->first();

				// Restore or create as needed
				if ($baserow)
				{
					if ($baserow->trashed())
					{
						$baserow->restore();
					}
				}
				else
				{
					$baserow = new UnixGroupMember;
					$baserow->unixgroupid = $base->id;
					$baserow->userid = $event->userrequest->userid;
					$baserow->notice = 0;
					$baserow->save();
				}
			}
		}
	}
}
