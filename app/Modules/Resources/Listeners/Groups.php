<?php

namespace App\Modules\Resources\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Groups\Events\MemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;
use App\Modules\Queues\Models\Queue;

/**
 * Group listener
 */
class Groups
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(MemberCreated::class, self::class . '@handleMemberCreated');
	}

	/**
	 * Auto-add group managers to any of the group's queues/resources
	 *
	 * @param   MemberCreated $event
	 * @return  void
	 */
	public function handleMemberCreated(MemberCreated $event): void
	{
		$member = $event->member;

		if (!$member->isManager())
		{
			return;
		}

		$q = (new Queue)->getTable();
		$s = (new Child)->getTable();
		$r = (new Asset)->getTable();

		$group = $member->group;

		if (!$group)
		{
			return;
		}

		// Get hosts this group has resources on
		$data = $group->queues()
			->withTrashed()
			->select($s . '.resourceid')
			->join($s, $s . '.subresourceid', $q . '.subresourceid')
			->join($r, $r . '.id', $s . '.resourceid')
			->whereNull($q . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->get();

		foreach ($data as $row)
		{
			// Look up the current resource
			$asset = Asset::findOrFail($row->resourceid);

			if (!$asset || $asset->trashed())
			{
				continue;
			}

			// Ignore if rolename is not set
			if (!$asset->rolename)
			{
				continue;
			}

			// Call central accounting service to request status
			event($resourcemember = new ResourceMemberStatus($asset, $member->user));

			if ($resourcemember->status <= 0)
			{
				throw new \Exception(__METHOD__ . '(): Bad status for `resourcemember` ' . $member->user);
			}

			if ($resourcemember->noStatus()
			 || $resourcemember->isPendingRemoval())
			{
				// Make call to resourcemember to generate role
				event($event = new ResourceMemberCreated($asset, $member->user));
			}
		}
	}
}
