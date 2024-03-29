<?php
namespace App\Modules\Storage\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Groups\Events\MemberCreated as GroupMemberCreated;
use App\Modules\Groups\Events\MemberDeleted as GroupMemberDeleted;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Notification;

/**
 * Storage listener for group members
 */
class GroupMembers
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(GroupMemberCreated::class, self::class . '@handleGroupMemberCreated');
		$events->listen(GroupMemberDeleted::class, self::class . '@handleGroupMemberDeleted');
	}

	/**
	 * Create alerts for this user for any directory that has a quota and is owned by this group
	 *
	 * @param   GroupMemberCreated  $event
	 * @return  void
	 */
	public function handleGroupMemberCreated(GroupMemberCreated $event): void
	{
		// Only perform the following for owners
		if (!$event->member->isManager())
		{
			return;
		}

		$dirs = Directory::query()
			->where('groupid', '=', $event->member->groupid)
			->where('bytes', '<>', 0)
			->get();

		if (!count($dirs))
		{
			return;
		}

		foreach ($dirs as $dir)
		{
			// First, check to see if user has or had any existing quota notifications.
			// We won't do anything if they already have an alert set up, or had one 
			// and deleted it (i.e., they 'opted-out' of the alerts).
			$notifications = $dir->notifications()
				->where('userid', '=', $event->member->userid)
				->withTrashed()
				->count();

			if (!$notifications)
			{
				$alert = new Notification;
				$alert->userid = $event->member->userid;
				$alert->storagedirquotanotificationtypeid = 3; // Space Threshold - Percent
				$alert->value = 99;
				$alert->storagedirid = $dir->id;
				$alert->save();

				$alert = new Notification;
				$alert->userid = $event->member->userid;
				$alert->storagedirquotanotificationtypeid = 3; // Space Threshold - Percent
				$alert->value = 80;
				$alert->storagedirid = $dir->id;
				$alert->save();
			}
		}
	}

	/**
	 * Remove storage notifications for user
	 *
	 * @param   GroupMemberDeleted  $event
	 * @return  void
	 */
	public function handleGroupMemberDeleted(GroupMemberDeleted $event): void
	{
		$dirs = Directory::query()
			->where('groupid', '=', $event->member->groupid)
			->where('bytes', '<>', 0)
			->get();

		if (!count($dirs))
		{
			return;
		}

		foreach ($dirs as $dir)
		{
			$notifications = $dir->notifications()
				->where('userid', '=', $event->member->userid)
				->get();

			foreach ($notifications as $n)
			{
				// Delete any storage dire quota notifications
				$n->delete();
			}
		}
	}
}
