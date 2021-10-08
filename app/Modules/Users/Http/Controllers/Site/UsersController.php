<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserLookup;
/*use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Notification;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Models\Member;*/

class UsersController extends Controller
{
	/**
	 * Show the specified resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function profile(Request $request)
	{
		$user = auth()->user();

		if (auth()->user()->can('manage users'))
		{
			if ($id = $request->input('u'))
			{
				if (is_numeric($id))
				{
					$user = User::findOrFail($id);
				}
				else
				{
					$user = User::findByUsername($id);

					if ((!$user || !$user->id) && config('module.users.create_on_search'))
					{
						event($event = new UserLookup(['username' => $id]));

						if (count($event->results))
						{
							$user = User::createFromUsername($id);
						}
					}
				}

				if (!$user || !$user->id)
				{
					abort(404);
				}
			}
		}

		event($event = new UserBeforeDisplay($user));
		$user = $event->getUser();

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			);

		event($event = new UserDisplay($user, $request->segment(2)));
		$sections = collect($event->getSections());

		return view('users::site.profile', [
			'user'     => $user,
			'sections' => $sections,
		]);
	}

	/**
	 * Show the specified resource.
	 * @return Response
	 */
	/*public function quotas(Request $request)
	{
		$user = auth()->user();

		if (auth()->user()->can('manage users'))
		{
			if ($id = $request->input('id'))
			{
				$user = User::findOrFail($id);
			}
		}

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			)
			->append(
				trans('users::users.quotas'),
				route('site.users.account.quotas')
			);

		$d = (new Directory)->getTable();
		$r = (new StorageResource)->getTable();
		$u = (new UnixGroupMember)->getTable();
		$g = (new Member)->getTable();

		$dirs = Directory::query()
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
			->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
			->join($r, $r . '.id', $d . '.storageresourceid')
			->join($u, $u . '.unixgroupid', $d . '.unixgroupid')
			->where($u . '.userid', '=', $user->id)
			->whereNull($d . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
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
			->whereNull($d . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->where($d . '.bytes', '<>', 0)
			->get();

		$dirs2 = Directory::query()
			->select($d . '.*', $r . '.path AS resourcepath', $r . '.id AS storageresourceid', $r . '.getquotatypeid')
			->join($r, $r . '.id', $d . '.storageresourceid')
			->join($u, $u . '.unixgroupid', $d . '.unixgroupid')
			->where($u . '.userid', '=', $user->id)
			->whereNull($d . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->where($u . '.datetimeremoved', '=', '0000-00-00 00:00:00')
			->where($d . '.bytes', '<>', 0)
			->get();

		$dirs3 = Directory::query()
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

		$storagenotifications = Notification::where('userid', '=', $user->id)->get();

		$path = __DIR__ . '/usage.csv';
		$delimiter = ',';

		ini_set('auto_detect_line_endings', true);

		$file = fopen($path, 'r');

		ini_set('auto_detect_line_endings', false);

		$headers = fgetcsv($file, 0, $delimiter);
		$count = 0;
		while (!feof($file))
		{
			$r = fgetcsv($file, 0, $delimiter);

			if (strtolower($r[0]) == 'id')
			{
				continue;
			}

			$usage = new \App\Modules\Storage\Models\Usage;
			$usage->id = $r[0];
			$usage->storagedirid = $r[1];
			$usage->quota = $r[2];
			$usage->filequota = $r[3];
			$usage->space = $r[4];
			$usage->files = $r[5];
			$usage->datetimerecorded = $r[6];
			$usage->lastinterval = $r[7];
			$usage->save();
		}
		fclose($file);

		return view('users::site.quotas', [
			'user' => $user,
			'storagedir' => $storagedirs,
			'storagedirquota' => $storagedirquota,
			'storagenotifications' => $storagenotifications,
		]);
	}*/

	/**
	 * Show the specified resource.
	 * @return Response
	 */
	/*public function groups(Request $request)
	{
		$user = auth()->user();

		if (auth()->user()->can('manage users'))
		{
			if ($id = $request->input('id'))
			{
				$user = User::findOrFail($id);
			}
		}

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			)
			->append(
				trans('users::users.groups'),
				route('site.users.account.groups')
			);

		$groups = $user->groups()->where('membertype', '=', 2)->where('groupid', '>', 0)->get();
		$select_tab = $request->input('g');

		if (!$select_tab)
		{
			$select_tab = $groups->first()->groupid;
		}

		return view('users::site.groups', [
			'user' => $user,
			'groups' => $groups,
			'select_tab' => $select_tab,
		]);
	}*/

	/**
	 * Show the specified resource.
	 * @return Response
	 */
	/*public function group(Request $request, $group)
	{
		$user = auth()->user();

		if (auth()->user()->can('manage users'))
		{
			if ($id = $request->input('id'))
			{
				$user = User::findOrFail($id);
			}
		}

		$g = $user->groups()
			->where('groupid', '=', $group)
			->first();

		if (!$g)
		{
			abort(404);
		}

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			)
			->append(
				trans('users::users.groups'),
				route('site.users.account.groups')
			)
			->append(
				$g->group->name,
				route('site.users.account.group', ['group' => $g->group->name])
			);

		return view('users::site.group', [
			'user' => $user,
			'g' => $g,
		]);
	}*/

	/**
	 * Show the specified resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function request(Request $request)
	{
		$user = auth()->user();

		event($event = new UserBeforeDisplay($user));
		$user = $event->getUser();

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			)
			->append(
				trans('users::users.request access'),
				route('site.users.account.request')
			);

		event($event = new UserDisplay($user, $request->segment(2)));
		$sections = collect($event->getSections());

		return view('users::site.request', [
			'user' => $user,
			'sections' => $sections,
		]);
	}
}
