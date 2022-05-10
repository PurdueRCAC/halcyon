<?php
namespace App\Listeners\Resources\StorageHome;

use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Models\Asset;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Directory;

/**
 * "Home" Storage listener for resources
 */
class StorageHome
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
	 * Create home dirs for users new to the resource
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		if (!$event->user || !$event->user->id)
		{
			return;
		}

		// Make sure it's a compute resource
		//
		// We do this because the StorageFortress plugin also triggers this
		// event and can cause a home dir get created for a compute resource
		// that doesn't want them.
		if ($event->resource->resourcetype != 1)
		{
			return;
		}

		// Get the Home resource
		//
		// Home directories can either be shared across multiple resources
		// or specific to a resource. Check which one we should load.
		$facet = $event->resource->getFacet('home');

		if ($facet && $facet->value != 'shared')
		{
			$home = Asset::query()
				->where('name', 'LIKE', '%Home')
				->where('parentid', '=', $event->resource->id)
				->first();
		}
		else
		{
			$home = Asset::query()
				->where('listname', '=', 'home')
				->first();
		}

		if (!$home)
		{
			return;
		}

		// Check if we have a storage dir already and create if not
		$dir = Directory::query()
			->where('name', '=', $event->user->username)
			->where('resourceid', '=', $home->id)
			->first();

		if ($dir)
		{
			return;
		}

		$storage = StorageResource::query()
			->where('parentresourceid', '=', $home->id)
			->first();

		// Prepare storagedir entry
		$dir = Directory::create([
			'resourceid'        => $home->id,
			'name'              => $event->user->username,
			'path'              => $event->user->username,
			'bytes'             => $storage->defaultquotaspace,
			'files'             => $storage->defaultquotafile,
			'owneruserid'       => $event->user->id,
			'storageresourceid' => $storage->id,
			'ownerread'         => 1,
			'ownerwrite'        => 1,
		]);

		// Prepare job to create directory in reality
		$dir->addMessageToQueue(11);
	}
}
