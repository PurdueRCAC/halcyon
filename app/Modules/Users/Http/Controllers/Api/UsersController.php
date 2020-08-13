<?php

namespace App\Modules\Users\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Users\Http\Resources\UserResourceCollection;
use App\Modules\Users\Http\Resources\UserResource;
use App\Modules\Users\Models\User;
use App\Modules\Users\Events\UserSearching;
use App\Halcyon\Access\Map;

/**
 * Users
 *
 * @apiUri    /api/users
 */
class UsersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/users
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return Response
	 */
	public function index(Request $request)//: UserResourceCollection
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'range' => null,
			'created_at' => null,
			'email_verified' => 1,
			'block'    => 0,
			//'access'   => 0,
			//'approved' => 1,
			'group_id' => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			// Sorting
			'order'     => 'created_at',
			'order_dir' => 'desc',
		);

		foreach ($filters as $key => $default)
		{
			// Check request
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name', 'username', 'email', 'access', 'created_at', 'lastVisitDate']))
		{
			$filters['order'] = 'created_at';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$a = (new User)->getTable();
		$b = (new Map)->getTable();

		$query = User::query()
			->select($a . '.*');
			//->with('accessgroups');
			/*->including(['notes', function ($note){
				$note
					->select('id')
					->select('user_id');
			}]);*/

		if ($filters['group_id'])
		{
			$entries
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->where($b . '.group_id', '=', (int)$filters['group_id']);
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
				$query->where(function($where) use ($filters, $a)
				{
					$search = strtolower((string)$filters['search']);

					$where->where($a . '.name', 'like', '%' . $search . '%')
						->orWhere($a . '.username', 'like', '%' . $search . '%')
						->orWhere($a . '.email', 'like', '%' . $search . '%');
				});
			}
		}

		if ($filters['created_at'])
		{
			$query->where($a . '.created_at', '>=', $filters['created_at']);
		}

		/*if ($filters['access'] > 0)
		{
			$query->where($a . '.access', '=', (int)$filters['access']);
		}*/

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
				$query->where($a . '.created_at', '<', $dStart->format('Y-m-d H:i:s'));
			}
			else
			{
				$query->where($a . '.created_at', '>=', $dStart->format('Y-m-d H:i:s'));
				$query->where($a . '.created_at', '<=', $dNow->format('Y-m-d H:i:s'));
			}
		}

		$rows = $query
			->orderBy($a . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		if (count($rows) < $filters['limit'] && $filters['search'])
		{
			event($event = new UserSearching($filters['search'], $rows));
			$rows = $event->getResults();
		}

		return $rows; //new UserResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/users
	 * @apiParameter {
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request): UserResource
	{
		$request->validate(array(
			'name' => 'required',
		));

		$user = User::create($request->all());

		return new UserResource($user);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/users/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function read($id): UserResource
	{
		$user = User::findOrFail($id);

		$user->notes;
		$user->roles;

		return new UserResource($user);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/users/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, User $user): UserResource
	{
		$request->validate([
			'fields.surname' => 'required',
			'fields.email' => 'required',
		]);

		//$id = $request->input('id');
		$fields = $request->input('fields');

		$user->fill($fields);

		// Can't block yourself
		if ($user->block && $user->id == auth()->user()->id)
		{
			throw new \Exception(trans('users::users.ERROR_CANNOT_BLOCK_SELF'));
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
				throw new \Exception(trans('users::users.ERROR_CANNOT_DEMOTE_SELF'));
			}
		}

		if (!$user->save())
		{
			$error = $user->getError() ? $user->getError() : trans('messages.save failed');

			throw new \Exception($error);
		}

		return new UserResource($user);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/users/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$user = User::findOrFail($id);

		if (!$user->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
