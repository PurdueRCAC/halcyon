<?php

namespace App\Modules\Groups\Listeners;

use App\Modules\Queues\Events\UserRequestUpdated;
use App\Modules\Groups\Models\UnixGroupMember;

/**
 * Group listener to add a user to a unix group
 */
class AddUserToUnixGroup
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param   Illuminate\Events\Dispatcher  $events
	 * @return  void
	 */
	public function subscribe($events)
	{
		$events->listen(UserRequestUpdated::class, self::class . '@handleUserRequestUpdated');
	}

	/**
	 * Handle an updated User Request
	 *
	 * @param   UserRequestUpdated  $event
	 * @return  void
	 */
	public function handleUserRequestUpdated(UserRequestUpdated $event)
	{
		if (!$event->userrequest)
		{
			return;
		}

		// Get base unix group
		$unixgroup = UnixGroup::query()
			->where('groupid', '=', $event->userrequest->queue->groupid)
			->where('shortname', 'like', config('module.groups.unix_prefix', 'rcs') . '%0')
			->get()
			->first();

		if (!$unixgroup)
		{
			return;
		}

		// Look for user's membership
		$item = UnixGroupMember::query()
			->where('userid', '=', $event->userrequest->userid)
			->where('unixgroupid', '=', $unixgroup->id)
			->get()
			->first();

		if ($item)
		{
			return;
		}

		// Need to create membership in base group
		$item = new UnixGroupMember;
		$item->userid = $event->userrequest->userid;
		$item->unixgroupid = $unixgroup->id;
		$item->save();
	}
}
