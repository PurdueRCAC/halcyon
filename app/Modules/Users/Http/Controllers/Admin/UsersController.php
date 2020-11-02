<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Access\Map;
use App\Halcyon\Access\Group as Role;
use App\Halcyon\Access\Gate;
use App\Halcyon\Access\Asset;
use Carbon\Carbon;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Helpers\Debug;

class UsersController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'     => null,
			'range'      => null,
			'created_at' => null,
			'activation' => 0,
			'state'      => 'enabled',
			'access'     => 0,
			'approved'   => '*',
			'role_id'    => 0,
			// Paging
			//'limit'     => config('list_limit', 20),
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc',
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('users.filter_' . $key, $key, $default);
		}

		$filters['limit'] = $request->state('users.limit', 'limit', config('list_limit', 20));
		$filters['page'] = $request->state('users.page', 'page', 1);

		if (!in_array($filters['order'], ['id', 'name', 'username', 'email', 'access', 'datecreated', 'lastVisitDate']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$a = (new User)->getTable();
		$b = (new Map)->getTable();
		$u = (new UserUsername)->getTable();

		$query = User::query()
			->select($a . '.*', $u . '.username', $u . '.datecreated', $u . '.dateremoved', $u . '.datelastseen')
			->with('roles')
			->join($u, $u . '.userid', $a . '.id');
			/*->including(['notes', function ($note){
				$note
					->select('id')
					->select('user_id');
			}]);*/

		if ($filters['role_id'])
		{
			$query
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->where($b . '.role_id', '=', (int)$filters['role_id']);
				/*->group($a . '.id')
				->group($a . '.name')
				->group($a . '.username')
				->group($a . '.password')
				->group($a . '.usertype')
				->group($a . '.block')
				->group($a . '.sendEmail')
				->group($a . '.created_at')
				->group($a . '.last_visit')
				->group($a . '.activation')
				->group($a . '.params')
				->group($a . '.email');*/
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($a . '.id', '=', (int)$filters['search']);
			}
			else
			{
				//$query->where($a . '.name', 'like', '%' . strtolower((string)$filters['search']) . '%');
				$query->where(function($where) use ($filters, $a, $u)
				{
					$where->where($a . '.name', 'like', '%' . strtolower((string)$filters['search']) . '%')
						->orWhere($u . '.username', 'like', '%' . strtolower((string)$filters['search']) . '%');
				});
			}
		}

		if ($filters['created_at'])
		{
			$query->where($u . '.datecreated', '>=', $filters['created_at']);
		}

		if ($filters['state'] == 'enabled')
		{
			$query->where(function($where) use ($u)
			{
				$where->whereNull($u . '.dateremoved')
					->orWhere($u . '.dateremoved', '=', '0000-00-00 00:00:00');
			});
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->where(function($where) use ($u)
			{
				$where->whereNotNull($u . '.dateremoved')
					->where($u . '.dateremoved', '!=', '0000-00-00 00:00:00');
			});
		}

		// Apply the range filter.
		if ($filters['range'])
		{
			// Get UTC for now.
			$dNow = Carbon::now();
			$dStart = clone $dNow;

			switch ($filters['range'])
			{
				case 'past_week':
					$dStart->modify('-7 day');
					break;

				case 'past_1month':
					$dStart->modify('-1 month');
					break;

				case 'past_3month':
					$dStart->modify('-3 month');
					break;

				case 'past_6month':
					$dStart->modify('-6 month');
					break;

				case 'post_year':
				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Reset the start time to be the beginning of today, local time.
					$dStart->setTime(0, 0, 0);
					break;
			}

			if ($filters['range'] == 'post_year')
			{
				$query->where($u . '.datecreated', '<', $dStart->format('Y-m-d H:i:s'));
			}
			else
			{
				$query->where($u . '.datecreated', '>=', $dStart->format('Y-m-d H:i:s'));
				$query->where($u . '.datecreated', '<=', $dNow->format('Y-m-d H:i:s'));
			}
		}

		$rows = $query
			->orderBy($a . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('users::admin.users.index', [
			'rows' => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function ingest()
	{
		$staff = Member::query()
			->where('group_id', '=', 1)
			->get()
			->pluck('user_id')
			->toArray();

		$users = app('db')
			->table('users_old')
			->limit(10000)
			->offset(4000)
			->orderBy('id', 'asc')
			->get();

		$now = Carbon::now();
		$processed = array();

		foreach ($users as $user)
		{
			if ($user->id == 1001)
			{
				continue;
			}

			$u = User::find($user->id);
			if (!$u)
			{
				$u = new User;
				$u->id = $user->id;
			}

			$u->username = 'unknown_user' . $user->id;

			$username = app('db')
				->table('userusernames')
				->where('userid', '=', $user->id)
				->orderBy('id', 'desc')
				->get()
				->first();

			$map = true;
			if ($username)
			{
				if (isset($processed[$username->username]))
				{
					$map = false;
					$u = User::find($processed[$username->username]);
				}
				else
				{
					$uf = User::findByUsername($username->username);
					if ($uf)
					{
						$map = false;
						$u = $uf;
					}
				}

				$u->username = $username->username;
				$u->email = $username->username . '@purdue.edu';
				$u->created_at = $now->toDateTimeString();
				if ($username->datecreated && $username->datecreated != '0000-00-00 00:00:00')
				{
					$u->created_at = $username->datecreated;
				}
				elseif ($username->datelastseen && $username->datelastseen != '0000-00-00 00:00:00')
				{
					$u->created_at = $username->datelastseen;
				}

				$u->email_verified_at = $u->created_at;
				//$u->deleted_at = $username->dateremoved;
				$u->block = ($username->dateremoved && $username->dateremoved != '0000-00-00 00:00:00') ? 1 : 0;
				$u->last_visit = ($username->datelastseen && $username->datelastseen != '0000-00-00 00:00:00') ? $username->datelastseen : null;
			}
			$u->name = $user->name;
			$u->organization_id = $u->puid ? $u->puid : 0;

			$bits = explode(' ', $u->name);
			$u->surname = '';
			$u->given_name = $u->name;
			$u->middle_name = '';
			if (count($bits) > 1)
			{
				$u->given_name = array_shift($bits);
				$u->surname = array_pop($bits);
				$u->middle_name = (count($bits) ? implode(' ', $bits) : '');
			}
			/*if ($map)
			{
				echo $u->id . ' - ' . $u->username . '<br />';
				continue;
			}*/
			$u->save();

			/*$map = new Map;
			$map->user_id = $u->id;
			$map->role_id = in_array($user->id, $staff) ? 3 : 2;
			$map->save();*/

			if ($map)
			{
				Map::addUserToRole($u->id, in_array($u->id, $staff) ? 3 : 2);
			}

			$processed[$u->username] = $u->id;
		}
		/*app('request')->merge(['hidemainmenu' => 1]);

		$row = new User;

		return view('news::admin.users.edit', [
			'row' => $row
		]);*/
		return $this->cancel();
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		$row = new User;

		return view('news::admin.users.edit', [
			'row' => $row
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required',
			//'fields.email' => 'required',
		]);

		$id = $request->input('id');

		$user = $id ? User::findOrFail($id) : new User();

		$fields = $request->input('fields');

		$user->fill($fields);

		if (!$user->id)
		{
			$newUsertype = config('modules.users.new_usertype');

			if (!$newUsertype)
			{
				$newUsertype = Role::findByTitle('Registered')->id;
			}

			$user->roles = array($newUsertype);

			$user->created_at = Carbon::now()->toDateTimeString();
		}

		// Can't block yourself
		if ($user->block && $user->id == auth()->user()->id)
		{
			return redirect()
				->back()
				->withError(trans('users::users.ERROR_CANNOT_BLOCK_SELF'));
		}

		// Make sure that we are not removing ourself from Super Admin role
		$iAmSuperAdmin = auth()->user()->can('admin');

		if ($iAmSuperAdmin && auth()->user()->id == $user->id)
		{
			// Check that at least one of our new roles is Super Admin
			$stillSuperAdmin = false;

			foreach ($fields['newroles'] as $role)
			{
				$stillSuperAdmin = ($stillSuperAdmin ? $stillSuperAdmin : Gate::checkRole($role, 'admin'));
			}

			if (!$stillSuperAdmin)
			{
				return redirect()
					->back()
					->withError(trans('users::users.ERROR_CANNOT_DEMOTE_SELF'));
			}
		}

		if (!$user->save())
		{
			$error = $user->getError() ? $user->getError() : trans('messages.save failed');

			return redirect()
				->back()
				->withError($error);
		}

		/*if (!$user->setRoles($fields['roles']))
		{
			$error = $user->getError() ? $user->getError() : trans('messages.save failed');

			return redirect()
				->back()
				->withError($error);
		}*/

		return $this->cancel()->with('success', trans('messages.item saved'));
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		$user = User::findOrFail($id);
		if ($user->puid)
		{
			$user->sourced = 1;
		}

		if (!$user->surname)
		{
			$bits = explode(' ', $user->name);

			$user->surname = array_pop($bits);
			if (count($bits) >= 1)
			{
				$user->given_name = array_shift($bits);
			}
			if (count($bits) >= 1)
			{
				$user->middle_name = implode(' ', $bits);
			}
		}

		return view('users::admin.users.edit', [
			'user' => $user
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = User::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.users.index'));
	}

	/**
	 * Sets the account blocked state of a member
	 *
	 * @return  Response
	 */
	public function unblock(Request $request)
	{
		return $this->block($request, 0);
	}

	/**
	 * Sets the account blocked state of a member
	 *
	 * @return  Response
	 */
	public function block(Request $request, $state = 1)
	{
		// Incoming user ID
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Do we have an ID?
		if (empty($ids))
		{
			$request->session()->flash('warning', trans('COM_MEMBERS_NO_ID'));

			return $this->cancel();
		}

		$i = 0;

		foreach ($ids as $id)
		{
			// Load the profile
			$user = User::findOrFail(intval($id));
			$user->block = $state;

			if ($user->block && $user->id == auth()->user()->id)
			{
				$request->session()->flash('error', trans('users::users.ERROR_CANNOT_BLOCK_SELF'));
				continue;
			}

			if (!$user->save())
			{
				$request->session()->flash('error', $user->getError());
				continue;
			}

			$i++;
		}

		if ($i)
		{
			$request->session()->flash('success', trans('COM_MEMBERS_BLOCK_STATUS_CHANGED'));
		}

		return $this->cancel();
	}

	/**
	 * Debug user permissions
	 *
	 * @return  void
	 */
	public function debug($id, Request $request)
	{
		// Get filters
		$filters = array(
			'search' => $request->input('search'),
			'order' => 'lft',
			'order_dir' => 'ASC',
			'level_start' => $request->input('filter_level_start', 0),
			'level_end' => $request->input('filter_level_end', 0),
			'module' => $request->input('filter_module'),
			'limit'     => config('list_limit', 20),
		);

		if ($filters['level_end'] > 0 && $filters['level_end'] < $filters['level_start'])
		{
			$filters['level_end'] = $filters['level_start'];
		}

		//$id = $request->input('id');

		// Load member
		$user = User::findOrFail($id);

		// Select the required fields from the table.
		$query = Asset::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', $filters['search'])
				->orWhere('title', 'like', $filters['search']);
		}

		if ($filters['level_start'] > 0)
		{
			$query->where('level', '>=', $filters['level_start']);
		}
		if ($filters['level_end'] > 0)
		{
			$query->where('level', '<=', $filters['level_end']);
		}

		// Filter the items over the component if set.
		if ($filters['module'])
		{
			$query->where('name', '=', $filters['module'])
				->orWhere('name', 'like', $filters['module']);
		}

		$assets = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		/*$actions = array(
			'Admin' => ['admin', null], //'Admin things'],
			'Manage' => ['manage', null], //'Manage things'],
			'Create' => ['create', null], //'Create things'],
			'Edit' => ['edit', null], //'Edit things'],
			'Edit state' => ['edit.state', null], //'Edit own things'],
			'Edit own' => ['edit.own', null], //'Edit own things'],
			'Delete' => ['delete', 'users::access.delete desc']
		);*/
		$actions = Debug::getActions($filters['module']);

		//$data = $assets->raw();
		//$assets->clear();

		$assets->map(function ($asset, $key) use ($user, $actions)
		{
			$checks = array();

			foreach ($actions as $action)
			{
				$name  = $action[0];
				/*$level = $action[1];

				// Check that we check this action for the level of the asset.
				if ($action[1] === null || $action[1] >= $asset->level)
				{
					// We need to test this action.
					//echo $id . ',' . $action[0] . ',' . $asset->name . '<br />';
					$checks[$name] = Gate::check($id, $action[0], $asset->name);
				}
				else
				{
					// We ignore this action.
					$checks[$name] = 'skip';
				}*/
				$checks[$name] = $user->can($name . ' ' . $asset->name);
			}

			$asset->checks = $checks;

			return $asset;
		});

		$levels  = Debug::getLevelsOptions();
		$modules = Debug::getModules();

		// Output the HTML
		return view('users::admin.users.debug', [
			'user' => $user,
			'filters' => $filters,
			'assets' => $assets,
			'actions' => $actions,
			'levels' => $levels,
			'modules' => $modules,
		]);
	}
}
