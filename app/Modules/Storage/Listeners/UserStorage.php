<?php

namespace App\Modules\Storage\Listeners;

use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Notification;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Models\Member;

/**
 * User listener for storage
 */
class UserStorage
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
	}

	/**
	 * Display user profile info
	 *
	 * @param   object  $event  UserBeforeDisplay
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$user = $event->getUser();
		$storagedirs = collect([]);
		$storagedirquotanotifications = collect([]);

		// Grab privately owned resources
		$dirs = Directory::query()
			->where('owneruserid', '=', $user->id)
			->where('parentstoragedirid', '=', 0)
			->withTrashed()
			->whereIsActive()
			->get();

		foreach ($dirs as $dir)
		{
			$dir->api = route('api.storage.directories.read', ['id' => $dir->id]);
			$dir->fullpath = $dir->fullPath;

			$dir->space = '-';
			$dir->quota = '-';
			$dir->files = '-';
			$dir->filequota = '-';
			$dir->lastcheck = '-';
			
			$usage = $dir->usage()->orderBy('datetimerecorded', 'desc')->first();

			if ($usage)
			{
				$dir->space = $usage->space;
				$dir->quota = $usage->quota;
				$dir->lastcheck = $usage->datetimerecorded;

				if ($usage->filequota != 0)
				{
					$dir->files = $usage->files;
					$dir->filequota = $usage->filequota;
				}
			}

			$storagedirs->add($dir);
		}

		$processed = $storagedirs->pluck('id')->toArray();

		// Grab high level group shared resources
		$d = (new Directory)->getTable();
		$ug = (new UnixGroupMember)->getTable();

		$dirs = Directory::query()
			->select($d . '.*')
			->join($ug, $ug . '.unixgroupid', $d . '.unixgroupid')
			->where($ug . '.userid', '=', $user->id)
			->where($d . '.bytes', '<>', 0)
			->withTrashed()
			->whereIsActive()
			->where(function($where) use ($ug)
			{
				$where->whereNull($ug . '.datetimeremoved')
					->orWhere($ug . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get();

		foreach ($dirs as $dir)
		{
			if (in_array($dir->id, $processed))
			{
				continue;
			}

			$dir->api = route('api.storage.directories.read', ['id' => $dir->id]);
			$dir->fullpath = $dir->fullPath;

			$dir->space = '-';
			$dir->quota = '-';
			$dir->files = '-';
			$dir->filequota = '-';
			$dir->lastcheck = '-';
			
			$usage = $dir->usage()->orderBy('datetimerecorded', 'desc')->first();

			if ($usage)
			{
				$dir->space = $usage->space;
				$dir->quota = $usage->quota;
				$dir->lastcheck = $usage->datetimerecorded;

				if ($usage->filequota != 0)
				{
					$dir->files = $usage->files;
					$dir->filequota = $usage->filequota;
				}
			}

			$storagedirs->add($dir);

			$processed[] = $dir->id;
		}

		// Grab directories owned by user
		$gu = (new Member)->getTable();

		$dirs = Directory::query()
			->select($d . '.*')
			->join($gu, $gu . '.groupid', $d . '.groupid')
			->where($gu . '.userid', '=', $user->id)
			->where($gu . '.membertype', '=', 2)
			->where($gu . '.groupid', '<>', 0)
			->where($d . '.bytes', '<>', 0)
			->withTrashed()
			->whereIsActive()
			->where(function($where) use ($gu)
			{
				$where->whereNull($gu . '.dateremoved')
					->orWhere($gu . '.dateremoved', '=', '0000-00-00 00:00:00');
			})
			->get();

		foreach ($dirs as $dir)
		{
			if (in_array($dir->id, $processed))
			{
				continue;
			}

			$dir->api = route('api.storage.directories.read', ['id' => $dir->id]);
			$dir->fullpath = $dir->fullPath;

			$dir->space = '-';
			$dir->quota = '-';
			$dir->files = '-';
			$dir->filequota = '-';
			$dir->lastcheck = '-';
			
			$usage = $dir->usage()->orderBy('datetimerecorded', 'desc')->first();

			if ($usage)
			{
				$dir->space = $usage->space;
				$dir->quota = $usage->quota;
				$dir->lastcheck = $usage->datetimerecorded;

				if ($usage->filequota != 0)
				{
					$dir->files = $usage->files;
					$dir->filequota = $usage->filequota;
				}
			}

			$storagedirs->add($dir);

			$processed[] = $dir->id;
		}

		$user->storagedirs = $storagedirs;

		// Fetch alerts
		$n = (new Notification)->getTable();
		$nots = Notification::query()
			->join($d, $d . '.id', $n . '.storagedirid')
			->select($n . '.*')
			->withTrashed()
			->whereIsActive()
			->where($n . '.userid', '=', $user->id)
			->where(function($where) use ($d)
			{
				$where->whereNull($d . '.datetimeremoved')
					->orWhere($d . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get();

		foreach ($nots as $not)
		{
			$storagedirquotanotifications->add($not);
		}

		$user->storagedirquotanotifications = $storagedirquotanotifications;

		$event->setUser($user);
	}
}
