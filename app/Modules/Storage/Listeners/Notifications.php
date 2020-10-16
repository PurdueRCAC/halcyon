<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Storage\Listeners;

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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(DirectoryCreated::class, self::class . '@handleDirectoryCreated');
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event)
	{
		$row = $event->directory;

		if ($row->bytes)
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
						'storagedirquotanotificationtypeid' => 3,
						'userid' => $member->userid,
						'value' => 99,
					]);
				}
			}

			// Create 80% and 99% alert for existing managers
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
						'storagedirquotanotificationtypeid' => 3,
						'userid' => $member->userid,
						'value' => 99,
					]);
				}
			}
		}
	}
}
