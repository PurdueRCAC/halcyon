<?php

namespace App\Modules\Groups\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserDeleted;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Events\MemberDeleted;

/**
 * Group listener to remove memberships when a user is removed
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
	 * Handle removal of a user account
	 *
	 * @param   UserDeleted  $event
	 * @return  void
	 */
	public function handleUserDeleted(UserDeleted $event): void
	{
		Member::query()
			->whereIn('userid', $event->user->id)
			->delete();

		UnixGroupMember::query()
			->whereIn('userid', $event->user->id)
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
		UnixGroupMember::query()
			->whereIn('userid', $event->member->userid)
			->delete();
	}
}
