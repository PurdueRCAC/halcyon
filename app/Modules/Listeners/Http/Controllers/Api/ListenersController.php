<?php

namespace App\Modules\Listeners\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Listeners\Models\Listener;
use App\Modules\Listeners\Models\Menu;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Viewlevel;

class ListenersController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'position'  => null,
			'listener'    => null,
			'language'  => null,
			'client_id' => 0,
			// Pagination
			'limit'     => config('list_limit', 20),
			'order'     => Listener::$orderBy,
			'order_dir' => Listener::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'position', 'state', 'access']))
		{
			$filters['order'] = Listener::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Listener::$orderDir;
		}

		$rows = Listener::paginate($filters['limit']);

		$query = Listener::query();

		$p = (new Listener)->getTable();
		$u = (new User)->getTable();
		$a = (new Viewlevel)->getTable();
		$m = (new Menu)->getTable();
		$e = 'extensions';
		$l = 'languages';

		$query->select(
				$p . '.*',
				$l . '.title AS language_title',
				$u . '.name AS editor',
				$a . '.title AS access_level',
				DB::raw('MIN(' . $m . '.menuid) AS pages'),
				$e . '.name AS name'
			)
			->where($p . '.client_id', '=', $filters['client_id']);

		// Join over the language
		$query
			//->select($l . '.title AS language_title')
			->leftJoin($l, $l . '.lang_code', $p . '.language');

		// Join over the users for the checked out user.
		$query
			//->select($u . '.name AS editor')
			->leftJoin($u, $u . '.id', $p . '.checked_out');

		// Join over the access groups.
		$query
			//->select($a . '.title AS access_level')
			->leftJoin($a, $a . '.id', $p . '.access');

		// Join over the access groups.
		$query
			//->select('MIN(' . $m . '.menuid) AS pages')
			->leftJoin($m, $m . '.moduleid', $p . '.id');

		// Join over the extensions
		$query
			//->select($e . '.name AS name')
			->join($e, $e . '.element', $p . '.module', 'left')
			->groupBy(
				$p . '.id',
				$p . '.title',
				$p . '.note',
				$p . '.position',
				$p . '.module',
				$p . '.language',
				$p . '.checked_out',
				$p . '.checked_out_time',
				$p . '.published',
				$p . '.access',
				$p . '.ordering',
				//$l . '.title',
				$u . '.name',
				$a . '.title',
				$e . '.name',
				//$l . '.lang_code',
				$u . '.id',
				$a . '.id',
				$m . '.moduleid',
				$e . '.element',
				$p . '.publish_up',
				$p . '.publish_down',
				$e . '.enabled'
			);

		// Filter by access level.
		if ($filters['access'])
		{
			$query->where($p . '.access', '=', (int) $filters['access']);
		}

		// Filter by published state
		/*if (is_numeric($filters['state']))
		{
			$query->where($p . '.published', '=', (int) $filters['state']);
		}
		elseif ($filters['state'] === '')
		{
			$query->whereIn($p . '.published', array(0, 1));
		}*/
		if ($filters['state'] == 'published')
		{
			$query->where($p . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($p . '.published', '=', 0);
		}

		// Filter by position.
		if ($filters['position'])
		{
			if ($filters['position'] == 'none')
			{
				$filters['position'] = '';
			}
			$query->where($p . '.position', '=', $filters['position']);
		}

		// Filter by module.
		if ($filters['listener'])
		{
			$query->where($p . '.module', '=', $filters['listener']);
		}

		// Filter by search
		if (!empty($filters['search']))
		{
			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($p . '.id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($where) use ($p, $filters)
				{
					$where->where($p . '.title', 'like', '%' . $filters['search'] . '%')
						->orWhere($p . '.note', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		// Filter by module.
		if ($filters['language'])
		{
			$query->where($p . '.language', '=', $filters['language']);
		}

		// Order records
		if ($filters['order'] == 'name')
		{
			$query->orderBy('name', $filters['order_dir']);
			$query->orderBy('ordering', 'asc');
		}
		else if ($filters['order'] == 'ordering')
		{
			$query->orderBy('position', 'asc');
			$query->orderBy('ordering', $filters['order_dir']);
			$query->orderBy('name', 'asc');
		}
		else
		{
			$query->orderBy($filters['order'], $filters['order_dir']);
			$query->orderBy('name', 'asc');
			$query->orderBy('ordering', 'asc');
		}

		$rows = $query
			->paginate($filters['limit']);

		$rows->appends(array_filter($filters));

		return $rows;
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'body' => 'required'
		]);

		$row = new Listener($request->all());

		if (!$row->save())
		{
			throw new \Exception($row->getError(), 409);
		}

		return $row;
	}

	/**
	 * Retrieve a specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Listener::findOrFail((int)$id);

		$row->api = route('api.listeners.read', ['id' => $row->id]);
		$row->menu_assignment = $row->menuAssignment();

		// Permissions check
		//$item->canCreate = false;
		$row->canEdit   = false;
		$row->canDelete = false;

		if (auth()->user())
		{
			//$item->canCreate = auth()->user()->can('create listeners');
			$row->canEdit   = auth()->user()->can('edit listeners');
			$row->canDelete = auth()->user()->can('delete listeners');
		}

		return $row;
	}

	/**
	 * Article the specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'title' => 'required',
			'position' => 'required'
		]);

		$row = Listener::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			throw new \Exception($row->getError(), 409);
		}

		return $row;
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
	 */
	public function destroy($id)
	{
		$row = Listener::findOrFail($id);

		if (!$row->delete())
		{
			throw new \Exception(trans('global.messages.delete failed', ['id' => $id]), 409);
		}

		return response()->json(null, 204);
	}
}
