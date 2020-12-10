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
	 * @param  Illuminate\Events\Dispatcher  $events
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

		if ($event->getActive() == 'quotas')
		{
			$d = (new Directory)->getTable();
			$r = (new StorageResource)->getTable();
			$u = (new UnixGroupMember)->getTable();
			$g = (new Member)->getTable();

			$dirs = Directory::query()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->where($d . '.owneruserid', '=', $user->id)
				->where($d . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($r . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where(function($where) use ($d, $r)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
				->get();

			$dirs2 = Directory::query()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($u, $u . '.unixgroupid', $d . '.unixgroupid')
				->where($u . '.userid', '=', $user->id)
				->where($d . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($r . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($u . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where(function($where) use ($d, $r)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
				->get();

			$storagedirquota = $dirs->merge($dirs2);

			$dirs = Directory::query()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->where($d . '.owneruserid', '=', $user->id)
				->where($d . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($r . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($d . '.bytes', '<>', 0)
				->get();

			$dirs2 = Directory::query()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($u, $u . '.unixgroupid', $d . '.unixgroupid')
				->where($u . '.userid', '=', $user->id)
				->where($d . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($r . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($u . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($d . '.bytes', '<>', 0)
				->get();

			$dirs3 = Directory::query()
				->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
				->join($r, $r . '.id', $d . '.storageresourceid')
				->join($g, $g . '.groupid', $d . '.groupid')
				->where($g . '.userid', '=', $user->id)
				->where($d . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($r . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($d . '.bytes', '<>', 0)
				->where($g . '.membertype', '=', 2)
				->where($g . '.groupid', '<>', 0)
				->get();

			$storagedirs = $dirs3->merge($dirs->merge($dirs2));

			$storagenotifications = Notification::where('userid', '=', $user->id)->get();

			app('pathway')
				->append(
					trans('storage::storage.my quotas'),
					route('site.users.account.section', $rt)
				);

			$content = view('storage::site.profile', [
				'user' => $user,
				'storagedir' => $storagedirs,
				'storagedirquota' => $storagedirquota,
				'storagenotifications' => $storagenotifications,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $rt),
			trans('storage::storage.my quotas'),
			($event->getActive() == 'quotas'),
			$content
		);
	}
}
