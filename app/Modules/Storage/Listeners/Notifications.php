<?php

namespace App\Modules\Storage\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Storage\Models\Notification;
use App\Modules\Storage\Events\DirectoryCreated;

/**
 * Notifications listener
 */
class Notifications
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(DirectoryCreated::class, self::class . '@handleDirectoryCreated');
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   DirectoryCreated  $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event): void
	{
		$row = $event->directory;

		if (!$row->bytes)
		{
			return;
		}

		if ($row->unixgroup)
		{
			// Create 99% alert for existing users
			$members = $row->unixgroup->members;

			foreach ($members as $member)
			{
				$notifications = $row->notifications()
					->where('userid', '=', $member->userid)
					->count();

				if (!$notifications)
				{
					Notification::create([
						'storagedirid' => $row->id,
						'storagedirquotanotificationtypeid' => 3, // Space Threshold - Percent
						'userid' => $member->userid,
						'value' => 99,
					]);
				}
			}
		}

		// Create 80% and 99% alert for existing managers
		if ($row->group)
		{
			$managers = $row->group->managers;

			foreach ($managers as $member)
			{
				$notifications = $row->notifications()
					->where('userid', '=', $member->userid)
					->count();

				if (!$notifications)
				{
					Notification::create([
						'storagedirid' => $row->id,
						'storagedirquotanotificationtypeid' => 3, // Space Threshold - Percent
						'userid' => $member->userid,
						'value' => 99,
					]);
				}
			}
		}
	}
}
