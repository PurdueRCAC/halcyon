<?php
namespace App\Listeners\Storage\Depot;

use App\Modules\Storage\Events\DirectoryCreated;
use App\Modules\Storage\Events\DirectoryUpdated;
use App\Modules\Storage\Events\DirectoryDeleted;
use App\Modules\Storage\Events\LoanCreated;
use App\Modules\Storage\Events\PurchaseCreated;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Resources\Models\Asset;
use App\Modules\Messages\Models\Type as MessageType;

/**
 * Depot listener for Storage
 */
class Depot
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(DirectoryCreated::class, self::class . '@handleDirectoryCreated');
		$events->listen(DirectoryUpdated::class, self::class . '@handleDirectoryUpdated');
		$events->listen(DirectoryDeleted::class, self::class . '@handleDirectoryDeleted');
		$events->listen(LoanCreated::class, self::class . '@handleLoanCreated');
		$events->listen(PurchaseCreated::class, self::class . '@handlePurchaseCreated');
	}

	/**
	 * Get the resource's ID
	 *
	 * @return  int
	 */
	private function getResourceId()
	{
		$asset = Asset::findByName('depot');
		return $asset ? $asset->id : 64;
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   DirectoryCreated   $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event)
	{
		$dir = $event->directory;

		// Create bonus thing if we're Depot, and root directory
		if ($dir->resourceid != $this->getResourceId())
		{
			return;
		}

		$this->addNewDirMessages($dir);

		if (!$dir->bytes || $dir->parentstoragedirid)
		{
			return;
		}

		// Get "-data" directory
		$unixgroups = UnixGroup::query()
			->where('groupid', '=', $dir->groupid)
			->where('shortname', 'like', '%1')
			->first();

		if (!$unixgroups)
		{
			return;
		}

		$data = $dir->toArray();
		foreach (['id', 'datetimecreated', 'datetimeremoved', 'datetimeconfigured'] as $key)
		{
			if (isset($data[$key]))
			{
				unset($data[$key]);
			}
		}

		$fortress = Asset::findByName('fortress');

		$dr = new Directory;
		$dr->fill($data);
		$dr->resourceid = $fortress ? $fortress->id : 48;

		$storage = StorageResource::query()
			->where('parentresourceid', '=', $dr->resourceid)
			->limit(1)
			->get()
			->first();

		$dr->storageresourceid = $storage ? $storage->id : 1;
		$dr->parentstoragedirid = 0;
		$dr->unixgroupid = $unixgroups->id;
		$dr->owneruserid = 0;
		$dr->bytes       = 0;
		$dr->files       = 0;
		$dr->groupread   = 1;
		$dr->groupwrite  = 1;
		$dr->publicread  = 0;
		$dr->publicwrite = 0;
		$dr->save();

		// Get "base" directory
		$unixgroups = UnixGroup::query()
			->where('groupid', '=', $dir->groupid)
			->where('shortname', 'like', '%0')
			->first();

		if (!$unixgroups)
		{
			return;
		}

		$box = Asset::findByName('boxfolder');

		$dr = new Directory;
		$dr->fill($data);
		$dr->resourceid = $box ? $box->id : 93; // Box Research Lab Folder

		$storage = StorageResource::query()
			->where('parentresourceid', '=', $dr->resourceid)
			->limit(1)
			->get()
			->first();

		$dr->storageresourceid = $storage ? $storage->id : 21;
		$dr->parentstoragedirid = 0;
		$dr->unixgroupid = $unixgroups->id;
		$dr->owneruserid = 0;
		$dr->bytes       = 0;
		$dr->files       = 0;
		$dr->path        = '[L1FR] ' . $dir->path;
		$dr->name        = '[L1FR] ' . $dir->name;
		$dr->save();
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   DirectoryUpdated  $event
	 * @return  void
	 */
	public function handleDirectoryUpdated(DirectoryUpdated $event)
	{
		$dir = $event->directory;

		if ($dir->resourceid != $this->getResourceId())
		{
			return;
		}

		$this->addNewDirMessages($dir);
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   LoanCreated  $event
	 * @return  void
	 */
	public function handleLoanCreated(LoanCreated $event)
	{
		$loan = $event->loan;
		$depotid = $this->getResourceId();

		if ($loan->resourceid != $depotid || $loan->bytes <= 0)
		{
			return;
		}

		$dir = Directory::query()
			->where('parentstoragedirid', '=', 0)
			->where('groupid', '=', $loan->groupid)
			->where('resourceid', '=', $depotid)
			->first();

		if (!$dir)
		{
			return;
		}

		if (!$dir->bytes)
		{
			$bucket = null;
			foreach ($loan->group->storagebuckets as $b)
			{
				if ($b['resourceid'] == $loan->resourceid)
				{
					$bucket = $b;
					break;
				}
			}

			if ($bucket == null)
			{
				$bytes = $loan->bytes;
			}
			else
			{
				$bytes = $bucket['unallocatedbytes'] + $dir->bytes;
			}

			$dir->bytes = $bytes;
			$dir->save();
		}
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   PurchaseCreated  $event
	 * @return  void
	 */
	public function handlePurchaseCreated(PurchaseCreated $event)
	{
		$purchase = $event->purchase;
		$depotid = $this->getResourceId();

		if ($purchase->resourceid != $depotid || $purchase->bytes <= 0)
		{
			return;
		}

		$dir = Directory::query()
			->where('parentstoragedirid', '=', 0)
			->where('groupid', '=', $purchase->groupid)
			->where('resourceid', '=', $depotid)
			->first();

		if (!$dir)
		{
			return;
		}

		if (!$dir->bytes)
		{
			$bucket = null;
			foreach ($purchase->group->storagebuckets as $b)
			{
				if ($b['resourceid'] == $purchase->resourceid)
				{
					$bucket = $b;
					break;
				}
			}

			if ($bucket == null)
			{
				$bytes = $purchase->bytes;
			}
			else
			{
				$bytes = $bucket['unallocatedbytes'] + $dir->bytes;
			}

			// Set the initial quota
			$dir->bytes = $bytes;
			$dir->save();
		}
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   object  $dir
	 * @return  void
	 */
	private function addNewDirMessages($dir)
	{
		if (!$dir->bytes && $dir->parent)
		{
			// Submit mkdir
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'mkdir %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}

		if ($dir->bytes)
		{
			// Submit filset create/sync
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'fileset %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}
	}

	/**
	 * Setup default notifications for removed directory
	 *
	 * @param   DirectoryDeleted  $event
	 * @return  void
	 */
	public function handleDirectoryDeleted(DirectoryDeleted $event)
	{
		$dir = $event->directory;

		if ($dir->resourceid != $this->getResourceId())
		{
			return;
		}

		if (!$dir->bytes && $dir->parent)
		{
			// Submit mkdir
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'rmdir %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}

		if ($dir->bytes)
		{
			// Submit filset create/sync
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'fileset %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}
	}
}
