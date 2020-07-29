<?php

namespace App\Modules\Users\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Halcyon\Access\Role;

class RolesController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'order'     => Role::$orderBy,
			'order_dir' => Role::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'title', 'lft']))
		{
			$filters['order'] = Role::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Role::$orderDir;
		}

		$query = Role::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where('title', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.users.roles.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
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
			'title' => 'required|string|max:100',
			'parent_id' => 'required|integer|min:1'
		]);

		$row = new Role;
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.users.roles.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve a specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Role::findOrFail((int)$id);

		$row->api = route('api.users.roles.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Type the specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'title' => 'nullable|string|max:100',
			'parent_id' => 'nullable|integer'
		]);

		$row = Role::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.users.roles.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Role::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
