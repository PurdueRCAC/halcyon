<?php
namespace App\Modules\Storage\Listeners;

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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
		$events->listen(UnixGroupMemberDeleted::class, self::class . '@handleUnixGroupMemberDeleted');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event)
	{
		// Check to see if we need to create storage dirs for this user
		$dirs = Directory::query()
			->where('autouserunixgroupid', '=', $event->member->unixgroupid)
			->where('autouser', '>', 0)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get();

		foreach ($dirs as $dir)
		{
			$userdir = Directory::query()
				->withTrashed()
				//->whereIsActive()
				->where('name', '=', $event->member->user->username)
				->where('parentstoragedirid', '=', $dir->id)
				->get();

			if (!$userdir)
			{
				$userdir = new Directory;
				$userdir->bytes       = '-';
				//$userdir->bytesource = '';
				$userdir->groupid     = $dir->groupid;
				$userdir->name        = $event->member->user->username;
				$userdir->parentstoragedirid = $dir->id;
				$userdir->resourceid  = $dir->resourceid;
				$userdir->unixgroupid = $event->member->unixgroupid;
				$userdir->owneruserid = $event->member->userid;

				if ($dir->autouser == '1')
				{
					// Group readable
					$userdir->groupread  = 1;
					$userdir->groupwrite = 0;
					$userdir->publicread  = 0;
				}
				elseif ($dir->autouser == '2')
				{
					// Private
					$userdir->groupread  = 0;
					$userdir->groupwrite = 0;
					$userdir->publicread  = 0;
				}

				$userdir->save();
			}
			elseif ($userdir->isTrashed())
			{
				$userdir->forceRestore(['datetimeremoved']);
			}
		}

		// Create alerts for this user for any directory that has a quota and is owned by this group
		$dirs = Directory::query()
			->withTrashed()
			->whereIsActive()
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
				$alert->storagedirquotanotificationtypeid = 3;
				$alert->value = 99;
				$alert->storagedirid = $dir->id;
				$alert->save();
			}
		}
	}

	/**
	 * Remove storage notifications for user
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberDeleted(UnixGroupMemberDeleted $event)
	{
		$dirs = Directory::query()
			->where('unixgroupid', '=', $event->member->unixgroupid)
			->where('bytes', '<>', 0)
			->withTrashed()
			->whereIsActive()
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
