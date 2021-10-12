<?php
namespace App\Listeners\Resources\StorageFortress;

use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Queues\Models\User as QueueUser;

/**
 * Fortress Storage listener for resources
 */
class StorageFortress
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(ResourceMemberCreated::class, self::class . '@handleResourceMemberCreated');
		$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		if ($event->resource->rolename == 'HPSSUSER')
		{
			return;
		}

		$resource = Asset::query()
			->where('rolename', '=', 'HPSSUSER')
			->first();

		if (!$resource)
		{
			return;
		}

		event($ev = new ResourceMemberStatus($resource, $event->user));

		if ($ev->status == 1 || $ev->status == 4)
		{
			event($ev = new ResourceMemberCreated($resource, $event->user));
		}

		if (!$event->user->id)
		{
			return;
		}

		// Add DB tracking entry for fortress
		$queueid = 33338;

		$qu = QueueUser::query()
			->where('queueid', '=', $queueid)
			->where('userid', '=', $event->user->id)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->first();

		if (!$qu)
		{
			// Need entry
			$qu = QueueUser::create([
				'queueid'    => $queueid,
				'userid'     => $event->user->id,
				'membertype' => 1,
			]);
		}
	}

	/**
	 * Perform some setup when a unix group member is created
	 *
	 * @param   UnixGroupMemberCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event)
	{
		$resource = Asset::query()
			->where('rolename', '=', 'HPSSUSER')
			->first();

		if (!$resource)
		{
			return;
		}

		// Check if they have the HPSS role, if not, give them that role
		event($ev = new ResourceMemberStatus($resource, $event->member->user));

		if ($ev->status == 1 || $ev->status == 4)
		{
			// Make call to role provision to generate role
			event($ev = new ResourceMemberCreated($resource, $event->member->user));
		}
	}
}
