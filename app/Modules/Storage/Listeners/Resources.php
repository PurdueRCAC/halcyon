<?php

namespace App\Modules\Storage\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Purchase;
use App\Modules\Storage\Models\Loan;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\AssetBeforeDisplay;
use App\Modules\Resources\Events\AssetDeleted;
use App\Modules\Resources\Models\Asset;

/**
 * Resources listener
 */
class Resources
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		//$events->listen(ResourceMemberCreated::class, self::class . '@handleResourceMemberCreated');
		$events->listen(AssetBeforeDisplay::class, self::class . '@handleAssetBeforeDisplay');
		$events->listen(AssetDeleted::class, self::class . '@handleAssetDeleted');
	}

	/**
	 * Trash storage resources when the parent resource
	 * is trashed.
	 *
	 * @param   AssetDeleted  $event
	 * @return  void
	 */
	public function handleAssetDeleted(AssetDeleted $event): void
	{
		$data = StorageResource::query()
			->where('parentresourceid', '=', $event->asset->id)
			->get();

		foreach ($data as $row)
		{
			$row->delete();
		}
	}

	/**
	 * Display user profile info
	 *
	 * @param   AssetBeforeDisplay  $event
	 * @return  void
	 */
	public function handleAssetBeforeDisplay(AssetBeforeDisplay $event): void
	{
		$asset = $event->getAsset();

		$storagebuckets = array();

		if ($asset->resourcetype == 2)
		{
			$rows = Purchase::query()
				->select(DB::raw('SUM(bytes) AS soldbytes'), 'groupid')
				->whenAvailable()
				->where('resourceid', '=', $asset->id)
				->groupBy('groupid')
				->get();

			foreach ($rows as $row)
			{
				$directory = Directory::query()
					->where('groupid', '=', $row->groupid)
					->where('resourceid', '=', $asset->id)
					->where('bytes', '!=', 0)
					->first();

				$storagebuckets[] = array(
					'soldbytes'   => (int)$row->soldbytes,
					'loanedbytes' => 0,
					'totalbytes'  => (int)$row->soldbytes,
					'path'        => $directory ? $directory->name : null,
					'group'       => $row->group
				);
			}

			$rows = Loan::query()
				->select(DB::raw('SUM(bytes) AS loanedbytes'), 'groupid')
				->whenAvailable()
				->where('resourceid', '=', $asset->id)
				->groupBy('groupid')
				->get();

			foreach ($rows as $row)
			{
				$found = false;

				foreach ($storagebuckets as &$bucket)
				{
					if ($bucket['group'] && $bucket['group']->id == $row->groupid)
					{
						$bucket['loanedbytes'] = (int)$row->loanedbytes;
						$bucket['totalbytes'] += (int)$row->loanedbytes;
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$directory = Directory::query()
						->where('groupid', '=', $row->groupid)
						->where('resourceid', '=', $asset->id)
						->where('bytes', '!=', 0)
						->first();

					$storagebuckets[] = array(
						'soldbytes'   => $row->loanedbytes,
						'loanedbytes' => 0,
						'totalbytes'  => $row->loanedbytes,
						'path'        => $directory ? $directory->name : null,
						'group'       => $row->group
					);
				}
			}
		}

		$asset->storagebuckets = $storagebuckets;

		$event->setAsset($asset);
	}

	/**
	 * Setup some directories for new resource members
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	/*public function handleResourceMemberCreated(ResourceMemberCreated $event): void
	{
		if (!$event->user->id)
		{
			return;
		}

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
			$directory = $row->directories()
				->where('name', '=', $event->user->username)
				->count();

			if ($directory)
			{
				continue;
			}

			$p = $event->user->username;

			// Are resource directories grouped by alphabet?
			if (in_array($event->resource->id, $alphabetical))
			{
				$l = substr($event->user->username, 0, 1);
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
				'ownerread'         => 1,
				'ownerwrite'        => 1,
			]);
			$directory->save();

			// Prepare job to create directory in reality
			if ($row->createtypeid)
			{
				$directory->addMessageToQueue($row->createtypeid);
			}
		}
	}*/
}
