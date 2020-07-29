<?php

namespace App\Modules\Menus\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Viewlevel;

class MenusController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			//'state'    => 'published',
			//'access'   => null,
			//'parent'   => 0,
			// Paging
			'limit'    => config('list_limit', 20),
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'published', 'access']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		// Get records
		$query = Type::query();

		if ($filters['search'])
		{
			$query->where(function($where) use ($filters)
			{
				$where->where('title', 'like', '%' . $filters['search'] . '%')
					->orWhere('description', 'like', '%' . $filters['search'] . '%');
			});
		}

		$rows = $query
			->withCount('items')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		$rows->appends(array_filter($filters));

		return $rows;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'title' => 'required',
			'menutype' => 'required',
		]);

		$row = new Type($request->all());

		if (!$row->save())
		{
			throw new \Exception($row->getError(), 409);
		}

		return $row;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   integer $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Type::findOrFail((int)$id);

		return $row;
	}

	/**
	 * Article the specified entry
	 *
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$row = Type::findOrFail($id);
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
	 * @param   integer $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Type::findOrFail($id);

		if (!$row->delete())
		{
			throw new \Exception(trans('global.messages.delete failed', ['id' => $id]), 409);
		}

		return response()->json(null, 204);
	}
}
