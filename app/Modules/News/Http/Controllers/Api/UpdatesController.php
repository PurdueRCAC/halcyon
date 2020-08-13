<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Update;

/**
 * Article Updates
 *
 * @apiUri    /api/news/updates
 */
class UpdatesController extends Controller
{
	/**
	 * Display a listing of news article updates
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news/updates
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "tagresources",
	 * 		"description":   "Filter by types that allow articles to tag resources",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "location",
	 * 		"description":   "Filter by types that allow articles to set location",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "location",
	 * 		"description":   "Filter by types that allow articles to set location",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "future",
	 * 		"description":   "Filter by types that allow articles to set future",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "ongoing",
	 * 		"description":   "Filter by types that allow articles to set ongoing",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
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
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return Response
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
	 * Create a news article update
	 *
	 * @apiMethod POST
	 * @apiUri    /api/news/updates
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "The comment being made",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the type",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagresources",
	 * 		"description":   "Allow articles to tag resources",
	 * 		"type":          "boolean",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "A URL associated with the news article",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiResponse {
	 * 		"id":            "1",
	 * 		"name":          "Examples",
	 * 		"tagresources":  0,
	 * 		"tagusers":      1,
	 * 		"location":      1,
	 * 		"future":        1,
	 * 		"calendar":      1,
	 * 		"url":           "https://example.com"
	 * }
	 * @param   Request  $request
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
	 * Read a news article update
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news/updates/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($news_id, $id)
	{
		$row = Update::findOrFail((int)$id);

		$row->api = route('api.news.updates.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a news article update
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/news/updates/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the type",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "A URL associated with the news article",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
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
	 * Delete a news article update
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/news/updates/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
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
		$row = Update::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
