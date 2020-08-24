<?php
namespace App\Listeners\Resources\Storage;

use App\Modules\Resources\Entities\Asset;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
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
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   object   $event
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
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
}
