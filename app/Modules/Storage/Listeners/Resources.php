<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Storage\Listeners;

use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Resources\Events\ResourceMemberCreated;

/**
 * Resources listener
 */
class Resources
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
	 * Setup some directories for new resource members
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		$alphabetical = config()->get('module.storage.alphabetical', [83, 86, 75, 76]);

		// Set up scratch dir if needed
		$data = StorageResource::query()
			->where('parentresourceid', '=', $event->resource->id)
			->where('autouserdir', '=', 1)
			->get();

		// If nothing, then we aren't managing scratch dirs
		foreach ($data as $row)
		{
			// First check if we have a storage dir already
			$directory = Directory::query()
				->where('name', '=', $event->user->username)
				->where('storageresourceid', '=', $row->id)
				->get()
				->first();

			if ($directory)
			{
				continue;
			}

			$l = substr($event->user->username, 0, 1);

			$p = $event->user->username;

			// Are resource directories grouped by alphabet?
			if (in_array($event->resource->id, $alphabetical))
			{
				$p = $l . '/' . $event->user->username;
			}

			// Prepare storagedir entry
			$directory = new Directory;
			$directory->fill([
				'resourceid'        => $event->resource->id,
				'name'              => $event->user->username,
				'path'              => $p,
				'bytes'             => $row->defaultquotaspace,
				'files'             => $row->defaultquotafile,
				'owneruserid'       => $event->user->id,
				'storageresourceid' => $row->id,
			]);
			$directory->save();

			// Prepare job to create directory in reality
			$directory->addMessageToQueue($row->createtypeid);
		}

		// Need to check for Home dir and create if necessary
		// First check if we have a storage dir already
		$directory = Directory::query()
			->where('name', '=', $event->user->username)
			->where('resourceid', '=', 81)
			->get()
			->first();

		if (!$directory)
		{
			return;
		}

		// Get values
		$storageResource = StorageResource::query()
			->where('name', '=', 'Home')
			->get()
			->first();

		// Create entry
		$directory = new Directory;
		$directory->fill([
			'resourceid'        => $storageResource->parentresourceid,
			'name'              => $event->user->username,
			'path'              => $p,
			'bytes'             => $row->defaultquotaspace,
			'files'             => $row->defaultquotafile,
			'owneruserid'       => $event->user->id,
			'storageresourceid' => $storageResource->id,
		]);
		$directory->save();

		// Prepare job to create directory in reality
		$directory->addMessageToQueue(11);
		/*$message = new Message;
		$message->fill([
			'messagequeuetypeid' => 11,
			'targetobjectid'     => $directory->id,
		]);
		$message->save();*/
	}
}
