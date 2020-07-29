<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Update;

class UpdatesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index($news_id, Request $request)
	{
		$article = Article::findOrFail($news_id);

		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'order'     => Update::$orderBy,
			'order_dir' => Update::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Update::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Update::$orderDir;
		}

		$query = $article->updates();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.news.updates.read', ['id' => $item->id]);
		});

		return $rows;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  Response
	 */
	public function create()
	{
		$request->validate([
			'body' => 'required|string',
			'newsid' => 'required|integer|min:1',
		]);

		$row = new Update;
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('page::messages.page created')], 500);
		}

		$row->api = route('api.news.updates.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve a specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function read($news_id, $id)
	{
		$row = Update::findOrFail((int)$id);

		$row->api = route('api.news.updates.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($news_id, $id, Request $request)
	{
		$request->validate([
			'body' => 'required',
			'newsid' => 'required',
		]);

		$row = Update::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('page::messages.page created')], 500);
		}

		$row->api = route('api.news.updates.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Update::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
