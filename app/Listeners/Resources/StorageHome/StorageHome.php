<?php
namespace App\Listeners\Resources\StorageHome;

use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Models\Asset;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Directory;

/**
 * Storage listener for resources
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
	 * Plugin that loads module positions within content
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		// Need to check for Home dir and create if necessary
		// First check if we have a storage dir already
		if ($event->resource->home != 'shared')
		{
			$home = Asset::query()
				->where('name', '=', 'Home')
				->where('parentid', '=', $event->resource->id)
				->first();
		}
		else
		{
			$home = Asset::query()
				->where('name', '=', 'Home')
				->first();
		}

		if (!$home)
		{
			return;
		}

		$dir = Directory::query()
			->where('name', '=', $event->user->username)
			->where('resourceid', '=', $home->id)
			->first();

		if ($dir)
		{
			return;
		}

		// Get values
		$storage = StorageResource::query()
			->where('parentresourceid', '=', $home->id)
			->withTrashed()
			->whereIsActive()
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
		]);

		// Prepare job to create directory in reality
		$dir->addMessageToQueue(11);
	}
}
