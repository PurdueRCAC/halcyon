<?php
namespace App\Listeners\Resources\StorageScratch;

use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Directory;

/**
 * Storage listener for resources
 */
class StorageScratch
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
		// Set up scratch dir if needed
		$storages = StorageResource::query()
			->where('parentresourceid', '=', $event->resource->id)
			->where('autouserdir', '=', 1)
			->get();

		foreach ($storages as $storage)
		{
			// First check if we have a storage dir already
			$dir = $storage->directories()
				->where('name', '=', $event->user->username)
				->first();

			if ($dir)
			{
				continue;
			}

			// Some resources require alphabetical subdivision
			$p = $event->user->username;

			if (in_array($event->resource->id, array(83, 86, 75, 76)))
			{
				$l = substr($event->user->username, 0, 1);
				$p = $l . '/' . $p;
			}

			// Prepare storagedir entry
			$dir = Directory::create([
				'resourceid' => $event->resource->id,
				'name' => $event->user->username,
				'path' => $p,
				'bytes' => $storage->defaultquotaspace,
				'files' => $storage->defaultquotafile,
				'owneruserid' => $event->user->id,
				'storageresourceid' => $storage->id,
			]);

			// Prepare job to create directory in reality
			if ($storage->createtypeid)
			{
				$dir->addMessageToQueue($storage->createtypeid);
			}
		}
	}
}
