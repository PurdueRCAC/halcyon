<?php

namespace App\Modules\Groups\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Queues\Events\UserRequestUpdated;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Events\MemberUpdated;

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
		$events->listen(MemberUpdated::class, self::class . '@handleMemberUpdated');
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
					$baserow->notice = UnixGroupMember::NO_NOTICE;
					$baserow->save();
				}
			}
		}
	}

	/**
	 * Auto add a promoted group member to all unix groups and queues
	 *
	 * @param   MemberUpdated  $event
	 * @return  void
	 */
	public function handleMemberUpdated(MemberUpdated $event): void
	{
		if ($event->member->isManager()
		 && $event->member->getOriginal('membertype') != $event->member->membertype)
		{
			$group = $event->member->group;

			if (!$group || !$group->cascademanagers)
			{
				return;
			}

			foreach ($group->unixgroups as $unixgroup)
			{
				// Look for user's membership
				$ugm = UnixGroupMember::query()
					->withTrashed()
					->where('unixgroupid', '=', $unixgroup->id)
					->where('userid', '=', $event->member->userid)
					->first();

				// Restore or create as needed
				if ($ugm)
				{
					if ($ugm->trashed())
					{
						$ugm->restore();
					}
				}
				else
				{
					$ugm = new UnixGroupMember;
					$ugm->unixgroupid = $unixgroup->id;
					$ugm->userid = $event->member->userid;
					$ugm->notice = UnixGroupMember::NO_NOTICE;
					$ugm->save();
				}
			}

			foreach ($group->queues as $queue)
			{
				// Look for user's membership
				$qu = QueueUser::query()
					->withTrashed()
					->where('queueid', '=', $queue->id)
					->where('userid', '=', $event->member->userid)
					->first();

				// Restore or create as needed
				if ($qu)
				{
					if ($qu->trashed())
					{
						$qu->restore();
					}
				}
				else
				{
					$qu = new QueueUser;
					$qu->queueid = $queue->id;
					$qu->userid = $event->member->userid;
					$qu->doNotNotify();
					$qu->save();
				}
			}
		}
	}
}
