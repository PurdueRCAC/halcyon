<?php

namespace App\Modules\Groups\Listeners;

use App\Modules\Users\Events\UserDeleted;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\UnixGroupMember;

/**
 * Group listener to remove memberships when a user is removed
 */
class RemoveMembershipsForDeletedUser
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param   Illuminate\Events\Dispatcher  $events
	 * @return  void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDeleted::class, self::class . '@handleUserDeleted');
	}

	/**
	 * Handle an updated User Request
	 *
	 * @param   UserDeleted  $event
	 * @return  void
	 */
	public function handleUserDeleted(UserDeleted $event)
	{
		if (!$event->user || !$event->user->id)
		{
			return;
		}

		Member::query()
			->whereIn('userid', $event->user->id)
			->delete();

		UnixGroupMember::query()
			->whereIn('userid', $event->user->id)
			->get();
	}
}
