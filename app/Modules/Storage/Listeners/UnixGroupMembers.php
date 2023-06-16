<?php
namespace App\Modules\Storage\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Notification;

/**
 * Storage listener for unix group members
 */
class UnixGroupMembers
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
		$events->listen(UnixGroupMemberDeleted::class, self::class . '@handleUnixGroupMemberDeleted');
	}

	/**
	 * Handle when a user is added to a unix group
	 *
	 * Some directories need to auto-create a subdirectory for each user.
	 *
	 * @param   UnixGroupMemberCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event): void
	{
		// Check to see if we need to create storage dirs for this user
		$dirs = Directory::query()
			->where('autouserunixgroupid', '=', $event->member->unixgroupid)
			->where('autouser', '>', 0)
			->get();

		foreach ($dirs as $dir)
		{
			$userdir = Directory::query()
				->withTrashed()
				->where('name', '=', $event->member->user->username)
				->where('parentstoragedirid', '=', $dir->id)
				->first();

			if (!$userdir)
			{
				$userdir = new Directory;
				$userdir->bytes       = '-';
				//$userdir->bytesource = '';
				$userdir->groupid     = $dir->groupid;
				$userdir->name        = $event->member->user->username;
				$userdir->path        = $dir->path . '/' . $userdir->name;
				$userdir->parentstoragedirid = $dir->id;
				$userdir->resourceid  = $dir->resourceid;
				$userdir->unixgroupid = $event->member->unixgroupid;
				$userdir->owneruserid = $event->member->userid;
				$userdir->storageresourceid = $dir->storageresourceid;
				$userdir->ownerread   = 1;
				$userdir->ownerwrite  = 1;

				if ($dir->autouser == 1)
				{
					// Group readable
					$userdir->groupread  = 1;
					$userdir->groupwrite = 0;
					$userdir->publicread = 0;
				}
				elseif ($dir->autouser == 2)
				{
					// Private
					$userdir->groupread  = 0;
					$userdir->groupwrite = 0;
					$userdir->publicread = 0;
				}

				$userdir->save();
			}
			elseif ($userdir->trashed())
			{
				$userdir->restore();
			}
		}

		// Create alerts for this user for any directory that has a quota and is owned by this group
		$dirs = Directory::query()
			->where('unixgroupid', '=', $event->member->unixgroupid)
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
			}
		}
	}

	/**
	 * Remove storage notifications for user
	 *
	 * @param   UnixGroupMemberDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberDeleted(UnixGroupMemberDeleted $event): void
	{
		$dirs = Directory::query()
			->where('unixgroupid', '=', $event->member->unixgroupid)
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
