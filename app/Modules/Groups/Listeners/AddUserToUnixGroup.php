<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Groups\Listeners;

use App\Modules\Queues\Events\UserRequestUpdated;
use App\Modules\Groups\Models\UnixGroupMember;

/**
 * Group listener
 */
class AddUserToUnixGroup
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserRequestUpdated::class, self::class . '@handleUserRequestUpdated');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserRequestUpdated(UserRequestUpdated $event)
	{
		if (!$event->userrequest)
		{
			return;
		}

		$unixgroup = UnixGroup::query()
			->where('groupid', '=', $event->userrequest->queue->groupid)
			->where('shortname', 'like', config('modules.groups.unix_prefix', 'rcs') . '%0')
			->get()
			->first();

		if (!$unixgroup)
		{
			return;
		}

		$item = UnixGroupMember::query()
			->where('userid', '=', $event->userrequest->userid)
			->where('unixgroupid', '=', $unixgroup->id)
			->get()
			->first();

		if ($item)
		{
			return;
		}

		$item = new UnixGroupMember;
		$item->userid = $event->userrequest->userid;
		$item->unixgroupid = $unixgroup->id;
		$item->save();
	}
}
