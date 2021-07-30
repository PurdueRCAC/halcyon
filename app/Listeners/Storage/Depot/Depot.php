<?php
namespace App\Listeners\Storage\Depot;

use App\Modules\Storage\Events\DirectoryCreated;
use App\Modules\Storage\Events\DirectoryUpdated;
use App\Modules\Storage\Events\DirectoryDeleted;
use App\Modules\Storage\Events\LoanCreated;
use App\Modules\Storage\Events\PurchaseCreated;
use App\Modules\Storage\Models\Directory;
use App\Modules\Groups\Models\UnixGroup;
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
	 * Setup default notifications for new directory
	 *
	 * @param   DirectoryCreated   $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event)
	{
		$dir = $event->directory;

		// Create bonus thing if we're Depot, and root directory
		if ($dir->resourceid != 64)
		{
			return;
		}

		$this->addNewDirMessages($dir);

		if (!$dir->bytes || $dir->parentstoragedirid)
		{
			return;
		}

		$dir->resourceid = 48;
		$dir->storageresourceid = 1;
		$dir->parentstoragedirid = 0;

		// Get "-data" directory
		$unixgroups = UnixGroup::query()
			->withTrashed()
			->whereIsActive()
			->where('groupid', '=', $dir->groupid)
			->where('shortname', 'like', '%1')
			->first();

		if (!$unixgroups)
		{
			return;
		}

		$dir->unixgroupid = $unixgroups->id;
		$dir->owneruserid = 0;
		$dir->bytes       = 0;
		$dir->files       = 0;
		$dir->groupread   = 1;
		$dir->groupwrite  = 1;
		$dir->publicread  = 0;
		$dir->publicwrite = 0;
		$dir->id          = null;
		$dir->save();

		$dir->resourceid = 93;
		$dir->storageresourceid = 21;
		$dir->parentstoragedirid = 0;

		// Get "base" directory
		$unixgroups = UnixGroup::query()
			->withTrashed()
			->whereIsActive()
			->where('groupid', '=', $dir->groupid)
			->where('shortname', 'like', '%0')
			->first();

		if (!$unixgroups)
		{
			return;
		}

		$dir->unixgroup   = $unixgroups->id;
		$dir->owneruserid = 0;
		$dir->bytes       = 0;
		$dir->files       = 0;
		$dir->path        = '[L1FR] ' . $dir->path;
		$dir->name        = '[L1FR] ' . $dir->name;
		$dir->id          = null;
		$dir->save();
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

		if ($dir->resourceid != 64)
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

		if ($loan->resourceid != 64 || $loan->bytes <= 0)
		{
			return;
		}

		$dir = Directory::query()
			->withTrashed()
			->whereIsActive()
			->where('parentstoragedirid', '=', 0)
			->where('groupid', '=', $loan->groupid)
			->where('resourceid', '=', 64)
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

		if ($purchase->resourceid != 64 || $purchase->bytes <= 0)
		{
			return;
		}

		$dir = Directory::query()
			->withTrashed()
			->whereIsActive()
			->where('parentstoragedirid', '=', 0)
			->where('groupid', '=', $purchase->groupid)
			->where('resourceid', '=', 64)
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

		if ($dir->resourceid != 64)
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
