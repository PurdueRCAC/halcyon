<?php

namespace App\Modules\Users\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Users\Http\Resources\UserResourceCollection;
use App\Modules\Users\Http\Resources\UserResource;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Models\Facet;
use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Events\UserUpdated;
use App\Modules\Users\Events\UserDeleted;
use App\Halcyon\Access\Map;
use App\Halcyon\Access\Gate;

/**
 * Users
 *
 * @apiUri    /users
 */
class UsersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /users
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'     => null,
			'range'      => null,
			'created_at' => null,
			//'email_verified' => 1,
			//'block'    => 0,
			//'access'   => 0,
			//'approved' => 1,
			'role_id'   => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'datecreated',
			'order_dir' => 'desc',
		);

		foreach ($filters as $key => $default)
		{
			// Check request
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name', 'username', 'access', 'datecreated', 'datelastseen']))
		{
			$filters['order'] = 'datecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$a = (new User)->getTable();
		$u = (new UserUsername)->getTable();
		$b = (new Map)->getTable();

		$query = User::query()
			->select($a . '.id', $a . '.name', $a . '.puid')
			->join($u, $u . '.userid', $a . '.id')
			->whereNull($u . '.dateremoved');
			//->with('accessgroups');
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
				$entries->where($a . '.id', '=', (int)$filters['search']);
			}
			else
			{
				$query->select($a . '.id', $a . '.name', $a . '.puid', DB::raw("CASE WHEN " . $u . ".username='" . strtolower((string)$filters['search']) . "' THEN 100 ELSE 1 END AS score"))
					->orderBy('score', 'desc');
				$query->where(function($where) use ($filters, $a, $u)
				{
					$search = strtolower((string)$filters['search']);
					$skipmiddlename = preg_replace('/ /', '% ', $search);

					$where->where($u . '.username', '=', $search)
						->orWhere($u . '.username', 'like', $search . '%')
						->orWhere($u . '.username', 'like', '%' . $search . '%')
						->orWhere($a . '.name', 'like', '% ' . $search . '%')
						->orWhere($a . '.name', 'like', $search . '%')
						->orWhere($a . '.name', 'like', '% ' . $skipmiddlename . '%')
						->orWhere($a . '.name', 'like', $skipmiddlename . '%');
						//->orWhere($u . '.email', 'like', '%' . $search . '%');
				});
			}
		}

		if ($filters['created_at'])
		{
			$query->where($a . '.datecreated', '>=', $filters['created_at']);
		}

		/*if ($filters['access'] > 0)
		{
			$query->where($a . '.access', '=', (int)$filters['access']);
		}

		if (is_numeric($filters['block']))
		{
			$query->where($a . '.block', '=', (int)$filters['block']);
		}

		if (!$filters['email_verified'] && auth()->user() && auth()->user()->can('manage users'))
		{
			$query->whereNull($a . '.email_verified_at');
		}
		else
		{
			$query->whereNotNull($a . '.email_verified_at');
		}*/

		// Apply the range filter.
		if ($filters['range'])
		{
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

		if ($filters['order'] == 'datecreated'
		 || $filters['order'] == 'username')
		{
			$filters['order'] = $u . '.' . $filters['order'];
		}
		else
		{
			$filters['order'] = $a . '.' . $filters['order'];
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));
		
		foreach ($rows as $row)
		{
			$row->username;
			$row->email;
		}

		if (count($rows) < $filters['limit'] && $filters['search'])
		{
			event($event = new UserSearching($filters['search'], $rows));
			$rows = $event->getResults();
		}

		$rows->each(function($item, $key)
		{
			if ($item->id)
			{
				$item->api = route('api.users.read', ['id' => $item->id]);
				$item->email = $item->getUserUsername()->email;
			}
			$item->api_token = null;
		});

		return $rows; //new UserResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /users
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "User name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "email",
	 * 		"description":   "Email address",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "email"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate(array(
			'name' => 'required|string|max:128',
			'puid' => 'nullable|integer',
			'username' => 'nullable|string|max:16',
			'email' => 'nullable|string|max:255',
		));

		$user = new User;
		$user->name = $request->input('name');
		if ($request->has('puid'))
		{
			$user->puid = $request->inout('puid');
		}
		$user->save();

		if ($request->has('username'))
		{
			$username = new UserUsername;
			$username->userid = $user->id;
			$username->username = $request->input('username');
			if ($request->has('email'))
			{
				$username->email = $request->input('email');
			}
			$username->save();
		}

		return new UserResource($user);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /users/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "module_permissions",
	 * 		"description":   "A comma-separated list of modules to grab permissions from",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "news,users,resources"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read(Request $request, $id)
	{
		if ($id === 'me')
		{
			$user = auth()->user();
		}
		else
		{
			$user = User::findOrFail($id);
		}
		if ($request->has('module_permissions'))
		{
			$permissions = explode(',', $request->input('module_permissions'));
			$permissions = array_map('trim', $permissions);
			$user->module_permissions = $permissions;
		}
		return new UserResource($user);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /users/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "User name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 128
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "puid",
	 * 		"description":   "Organization ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "username",
	 * 		"description":   "Username",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 16
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "puid",
	 * 		"description":   "Organization ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datelastseen",
	 * 		"description":   "Last time the user logged in",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "roles",
	 * 		"description":   "A list of Role IDs to apply to the user",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|string|max:128',
			'puid' => 'nullable|integer',
			'username' => 'nullable|string|max:16',
			'unixid' => 'nullable|integer',
			'datelastseen' => 'nullable',
			'roles' => 'nullable|array',
			'facets' => 'nullable|array',
			'email' => 'nullable|string|max:255',
		]);

		$user = User::findOrFail($id);

		if ($request->has('name')
		 || $request->has('puid')
		 || $request->has('roles'))
		{
			$user->name = $request->input('name', $user->name);
			$user->puid = $request->input('puid', $user->puid);

			//$id = $request->input('id');
			//$fields = $request->input('fields');

			//$user->fill($fields);

			// Can't block yourself
			/*if ($user->block && $user->id == auth()->user()->id)
			{
				throw new \Exception(trans('users::users.error.cannot block self'));
			}*/

			if ($request->has('roles'))
			{
				$roles = $request->input('roles', []);

				// Make sure that we are not removing ourself from Super Admin role
				$iAmSuperAdmin = auth()->user()->can('admin');

				if ($iAmSuperAdmin && auth()->user()->id == $user->id)
				{
					// Check that at least one of our new roles is Super Admin
					$stillSuperAdmin = false;

					foreach ($roles as $role)
					{
						$stillSuperAdmin = ($stillSuperAdmin ? $stillSuperAdmin : Gate::checkRole($role, 'admin'));
					}

					if (!$stillSuperAdmin)
					{
						return response()->json(['message' => trans('users::users.error.cannot demote self')], 500);
					}
				}

				$user->newroles = $roles;
			}

			if (!$user->save())
			{
				return response()->json(['message' => trans('global.messages.save failed', ['id' => $id])], 500);
			}
		}

		if ($request->has('unixid')
		 || $request->has('username')
		 || $request->has('email')
		 || $request->has('datelastseen'))
		{
			$username = $user->getUserUsername();

			$username->unixid = $request->input('unixid', $username->unixid);
			$username->username = $request->input('datelastseen', $username->username);
			$username->email = $request->input('email', $username->email);
			$username->datelastseen = $request->input('datelastseen', $username->datelastseen);

			if (!$username->save())
			{
				return response()->json(['message' => trans('global.messages.save failed', ['id' => $id])], 500);
			}
		}

		if ($request->has('loginShell'))
		{
			$user->loginShell = $request->input('loginShell');

			// Check that the login shell is valid:
			//
			// shell is in /bin
			if ($user->loginShell != '/sbin/nologin'
			&& !preg_match('@^/bin/.*sh$@', $user->loginShell)
			&& !preg_match('@^/opt/acmaint.*$@', $user->loginShell)
			&& !preg_match('@^/usr/local/bin/.*sh$@', $user->loginShell))
			{
				return response()->json(['message' => trans('Field `loginshell` has invalid format')], 415);
			}

			// shell chosen exists
			if (!is_file($user->loginShell))
			{
				return response()->json(['message' => trans('`loginshell` is not a valid file')], 415);
			}

			// shell is executable
			if (!is_executable($user->loginShell))
			{
				return response()->json(['message' => trans('`loginshell` is not executable')], 415);
			}

			event(new UserUpdated($user));
		}

		if ($request->has('facets'))
		{
			$facets = $request->input('facets');

			foreach ($facets as $key => $value)
			{
				$facet = Facet::findByUserAndKey($user->id, $key);
				$facet = $facet ?: new Facet;
				$facet->user_id = $user->id;
				$facet->key     = $key;
				$facet->value   = $value;
				$facet->save();
			}
		}

		return new UserResource($user);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /users/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$user = User::findOrFail($id);

		if (!config('module.users.allow_self_deletion') && $user->id == auth()->user()->id)
		{
			return response()->json(['message' => trans('users::auth.self deletion not allowed', ['id' => $id])], 403);
		}

		foreach ($user->usernames as $username)
		{
			if (!$username->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		event(new UserDeleted($user));

		return response()->json(null, 204);
	}
}
