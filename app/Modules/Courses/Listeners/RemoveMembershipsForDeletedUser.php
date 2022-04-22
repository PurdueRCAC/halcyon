<?php

namespace App\Modules\Courses\Listeners;

use App\Modules\Users\Events\UserDeleted;
use App\Modules\Courses\Models\Member;

/**
 * Courses listener to remove memberships when a user is removed
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
	}
}
