<?php
namespace App\Modules\Storage\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Validation\ValidationException;
use App\Modules\Groups\Events\UnixGroupDeleting;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Resources\Models\Asset;

/**
 * Storage listener for unix groups
 */
class DeletingUnixGroup
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UnixGroupDeleting::class, self::class . '@handleUnixGroupDeleting');
	}

	/**
	 * Check how many storage directories are using the
	 * unix group being deleted.
	 *
	 * @param   UnixGroupDeleting  $event
	 * @return  void
	 * @throws  ValidationException
	 */
	public function handleUnixGroupDeleting(UnixGroupDeleting $event): void
	{
		$unixgroup = $event->unixgroup;

		$d = (new Directory)->getTable();
		$r = (new Asset)->getTable();
		$s = (new StorageResource)->getTable();

		$dirs = Directory::query()
			->where(function($where) use ($d, $unixgroup)
			{
				$where->where($d . '.autouserunixgroupid', '=', $unixgroup->id)
					->orWhere($d . '.unixgroupid', '=', $unixgroup->id);
			})
			->join($r, $r . '.id', $d . '.resourceid')
			->whereNull($r . '.datetimeremoved')
			->join($s, $s . '.id', $d . '.storageresourceid')
			->whereNull($s . '.datetimeremoved')
			->where($s . '.groupmanaged', '=', 1)
			->count();

		if ($dirs > 0)
		{
			// We need to throw an exception instead of simply returning false
			// in order to pass the error message.
			throw ValidationException::withMessages([
				'message' => trans_choice('storage::storage.error.directories found using unix group', $dirs, ['num' => $dirs])
			]);
		}
	}
}
