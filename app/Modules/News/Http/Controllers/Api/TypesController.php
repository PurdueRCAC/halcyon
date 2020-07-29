<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\News\Models\Type;

class TypesController extends Controller
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
			'tagresources' => null,
			'location'  => null,
			'future'    => null,
			'ongoing'   => null,
			'limit'     => config('list_limit', 20),
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		$query = Type::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if (!is_null($filters['tagresources']))
		{
			$query->where('tagresources', '=', $filters['tagresources']);
		}

		if (!is_null($filters['location']))
		{
			$query->where('location', '=', $filters['location']);
		}

		if (!is_null($filters['future']))
		{
			$query->where('future', '=', $filters['future']);
		}

		if (!is_null($filters['ongoing']))
		{
			$query->where('ongoing', '=', $filters['ongoing']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.news.types.read', ['id' => $item->id]);
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
			'name' => 'required'
		]);

		$row = new Type($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.news.types.read', ['id' => $row->id]);

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
		$row = Type::findOrFail((int)$id);

		$row->api = route('api.news.types.read', ['id' => $row->id]);

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
			'name' => 'required'
		]);

		$row = Type::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.news.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Type::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
