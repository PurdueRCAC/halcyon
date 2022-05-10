<?php
namespace App\Listeners\Resources\StorageFortress;

use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Queues\Models\User as QueueUser;

/**
 * Fortress Storage listener for resources
 * 
 * This ensures access to Fortress when a user is added to
 * some other resource and/or unix group.
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
	 * Setup access to Fortress for new resource members
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		$blacklist = config('listener.storagefortress.blacklist', ['anvil', 'internal']);
		$blacklist[] = 'HPSSUSER';

		// "HPSSUSER" is Fortress. Avoid a neverending loop.
		if (in_array($event->resource->rolename, $blacklist))
		{
			return;
		}

		if (!$event->user || !$event->user->id)
		{
			return;
		}

		// Create account role if needed
		if (!$this->ensureResourceMembership($event->user))
		{
			return;
		}

		// Add DB tracking entry for Fortress
		$queueid = 33338; // "Research Storage" queue for the ITaP group

		$qu = QueueUser::query()
			->where('queueid', '=', $queueid)
			->where('userid', '=', $event->user->id)
			->whereNull('datetimeremoved')
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
		$this->ensureResourceMembership($event->member->user);
	}

	/**
	 * Check if they have the HPSS role, if not, give them that role
	 *
	 * @param   User  $user
	 * @return  bool
	 */
	private function ensureResourceMembership($user)
	{
		$resource = Asset::query()
			->where('rolename', '=', 'HPSSUSER')
			->first();

		if (!$resource)
		{
			return false;
		}

		event($ev = new ResourceMemberStatus($resource, $user));

		// 1 == no role, 4 == removal pending
		if ($ev->status == 1 || $ev->status == 4)
		{
			// Make call to role provision to generate role
			event($ev = new ResourceMemberCreated($resource, $user));
		}

		return true;
	}
}
