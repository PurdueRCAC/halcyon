<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Update;
use App\Modules\News\Notifications\ArticleUpdated;
use App\Modules\News\Http\Resources\UpdateResource;
use App\Modules\News\Http\Resources\UpdateResourceCollection;

/**
 * Article Updates
 *
 * @apiUri    /news/updates
 */
class UpdatesController extends Controller
{
	/**
	 * Display a listing of news article updates
	 *
	 * @apiMethod GET
	 * @apiUri    /news/updates
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "newsid",
	 * 		"description":   "Filter by news article ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"datetimecreated",
	 * 				"datetimeremoved"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  integer  $news_id
	 * @param  Request  $request
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

		return new UpdateResourceCollection($rows);
	}

	/**
	 * Create a news article update
	 *
	 * @apiMethod POST
	 * @apiUri    /news/updates
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "The update being made",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "newsid",
	 * 		"description":   "News article ID",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"userid": 12344,
	 * 						"edituserid": 12345,
	 * 						"datetimecreated": "2021-01-27 20:03:51",
	 * 						"datetimeedited": null,
	 * 						"datetimeremoved": null,
	 * 						"body": "Example text",
	 * 						"newsid": 1
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $news_id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create($news_id, Request $request)
	{
		$rules = [
			'body' => 'required|string|max:15000',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Update;
		$row->newsid = $news_id;
		$row->body = $request->input('body');
		if (auth()->user())
		{
			$row->userid = auth()->user()->id;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('news::news.error.failed to create record')], 500);
		}

		if ($row->article->published && !$row->article->template && in_array($row->article->newstypeid, config('module.news.notify_update', [])))
		{
			$row->article->notify(new ArticleUpdated($row->article));
		}

		return new UpdateResource($row);
	}

	/**
	 * Read a news article update
	 *
	 * @apiMethod GET
	 * @apiUri    /news/updates/{id}
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
	 * 		"200": {
	 * 			"description": "Entry found",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"userid": 12344,
	 * 						"edituserid": 12345,
	 * 						"datetimecreated": "2021-01-27 20:03:51",
	 * 						"datetimeedited": null,
	 * 						"datetimeremoved": null,
	 * 						"body": "Example text",
	 * 						"newsid": 1
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $news_id
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($news_id, $id)
	{
		$row = Update::findOrFail((int)$id);

		return new UpdateResource($row);
	}

	/**
	 * Update a news article update
	 *
	 * @apiMethod PUT
	 * @apiUri    /news/updates/{id}
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
	 * 		"name":          "body",
	 * 		"description":   "Contents of the update",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"userid": 12344,
	 * 						"edituserid": 12345,
	 * 						"datetimecreated": "2021-01-27 20:03:51",
	 * 						"datetimeedited": "2021-01-28 13:40:01",
	 * 						"datetimeremoved": null,
	 * 						"body": "Example text that was edited",
	 * 						"newsid": 1
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $news_id
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($news_id, $id, Request $request)
	{
		$rules = [
			'body' => 'required|string|max:15000',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Update::findOrFail($id);
		$row->body = $request->input('body');
		if (auth()->user())
		{
			$row->edituserid = auth()->user()->id;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('news::news.error.failed to update record')], 500);
		}

		return new UpdateResource($row);
	}

	/**
	 * Delete a news article update
	 *
	 * @apiMethod DELETE
	 * @apiUri    /news/updates/{id}
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
	 * @param   integer  $news_id
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($news_id, $id)
	{
		$row = Update::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
