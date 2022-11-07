<?php
namespace App\Listeners\Users\Quotas;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Notification;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Models\Member;

/**
 * User listener for Quotas
 */
class Quotas
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$content = null;
		$user = $event->getUser();

		$rt = ['section' => 'quotas'];
		if (auth()->user()->id != $user->id)
		{
			$rt['u'] = $user->id;
		}

		if ($event->getActive() == 'quotas'  || $event->getActive() == 'myquota' || app('isAdmin'))
		{
			$d = (new Directory)->getTable();
			$r = (new StorageResource)->getTable();
			$u = (new UnixGroupMember)->getTable();
			$g = (new Member)->getTable();

			
			$dirs = Directory::query()
				->withTrashed()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->where($d . '.owneruserid', '=', $user->id)
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->where(function($where) use ($d, $r)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
				->get();

			
			$dirs2 = Directory::query()
				->withTrashed()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($u, $u . '.unixgroupid', $d . '.unixgroupid')
				->where($u . '.userid', '=', $user->id)
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->whereNull($u . '.datetimeremoved')
				->where(function($where) use ($d, $r)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
				->get();

			// Grab directories owned by user
			$dirs3 = Directory::query()
				->withTrashed()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($g, $g . '.groupid', $d . '.groupid')
				->where($g . '.userid', '=', $user->id)
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->where(function($where) use ($d, $r)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
				->where($g . '.membertype', '=', 2)
				->where($g . '.groupid', '<>', 0)
				->get();

			$storagedirquota = $dirs3->merge($dirs->merge($dirs2));

			// Grab privately owned resources
			$dirs = Directory::query()
				->withTrashed()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->where($d . '.owneruserid', '=', $user->id)
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->where($d . '.bytes', '<>', 0)
				->get();

			// Grab high level group shared resources
			$dirs2 = Directory::query()
				->withTrashed()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($u, $u . '.unixgroupid', $d . '.unixgroupid')
				->where($u . '.userid', '=', $user->id)
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->whereNull($u . '.datetimeremoved')
				->where($d . '.bytes', '<>', 0)
				->get();

			// Grab directories owned by user
			$dirs3 = Directory::query()
				->withTrashed()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($g, $g . '.groupid', $d . '.groupid')
				->where($g . '.userid', '=', $user->id)
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->where($d . '.bytes', '<>', 0)
				->where($g . '.membertype', '=', 2)
				->where($g . '.groupid', '<>', 0)
				->get();

			$storagedirs = $dirs3->merge($dirs->merge($dirs2));

			$storagenotifications = Notification::query()
				->where('userid', '=', $user->id)
				->get();

			if (!app('isAdmin'))
			{
				app('pathway')
					->append(
						trans('storage::storage.my quotas'),
						route('site.users.account.section', $rt)
					);
			}

			$content = view('storage::' . (app('isAdmin') ? 'admin.user' : 'site.profile'), [
				'user' => $user,
				'storagedirs' => $storagedirs,
				'storagedirquota' => $storagedirquota,
				'storagenotifications' => $storagenotifications,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $rt),
			trans('storage::storage.my quotas'),
			($event->getActive() == 'quotas' || $event->getActive() == 'myquota'),
			$content
		);
	}
}
