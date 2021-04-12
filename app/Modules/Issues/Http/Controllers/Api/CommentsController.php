<?php

namespace App\Modules\Issues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Comment;
use App\Modules\Issues\Http\Resources\CommentResource;
use App\Modules\Issues\Http\Resources\CommentResourceCollection;
use Carbon\Carbon;

/**
 * Comments
 *
 * @apiUri    /api/issues/comments
 */
class CommentsController extends Controller
{
	/**
	 * Display a listing of issues comments
	 *
	 * @apiMethod GET
	 * @apiUri    /api/issues/comments
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "issueid",
	 * 		"description":   "ID of issue",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'issueid'   => 0,
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'start'     => null,
			'stop'      => null,
			'page'      => 1,
			'order'     => Comment::$orderBy,
			'order_dir' => Comment::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Comment::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Comment::$orderDir;
		}

		$query = Comment::query()
			->withTrashed()
			->whereIsActive();

		if ($filters['issueid'])
		{
			$query->where('issueid', $filters['issueid']);
		}

		$cr = (new Comment)->getTable();

		if ($filters['search'])
		{
			$query->where('commment', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new CommentResourceCollection($rows);
	}

	/**
	 * Create a issue comment
	 *
	 * @apiMethod POST
	 * @apiUri    /api/issues/comments
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
	 * 		"name":          "issueid",
	 * 		"description":   "ID of the issue",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'comment' => 'nullable|string',
			'issueid' => 'required|integer',
		]);

		$row = new Comment($request->all());
		if (!$row->comment)
		{
			$row->comment = '';
		}
		$row->userid = auth()->user() ? auth()->user()->id : 0;

		if (!$row->issue)
		{
			return response()->json(['message' => __METHOD__ . '(): Invalid issue ID'], 415);
		}

		$row->datetimecreated = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new CommentResource($row);
	}

	/**
	 * Retrieve an issue comment
	 *
	 * @apiMethod GET
	 * @apiUri    /api/issues/comments/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($comment)
	{
		$item = Comment::findOrFail((int)$comment);

		return new CommentResource($item);
	}

	/**
	 * Update an issue comment
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/issues/comments/{id}
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
	 * 		"name":          "comment",
	 * 		"description":   "The comment being made",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "issueid",
	 * 		"description":   "ID of the issue",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $comment)
	{
		$request->validate([
			'comment' => 'nullable|string',
			'issueid' => 'nullable|integer',
			'userid' => 'nullable|integer',
			'notice' => 'nullable|integer',
		]);

		$data = $request->all();

		if (isset($data['datetimecreated']))
		{
			unset($data['datetimecreated']);
		}

		if (!auth()->user() || !auth()->user()->can('admin issues'))
		{
			unset($data['userid']);
		}

		$row = Comment::findOrFail($comment);
		$row->fill($data);

		if ($row->issueid != $row->getOriginal('issueid'))
		{
			if (!$row->issue)
			{
				return response()->json(['message' => __METHOD__ . '(): Invalid issue ID'], 415);
			}
		}

		if (!$row->comment)
		{
			return response()->json(['message' => __METHOD__ . '(): Comment text cannot be emoty'], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		return new CommentResource($row);
	}

	/**
	 * Delete an issue comment
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/issues/delete/{id}
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
	 * @param   integer  $comment
	 * @return  Response
	 */
	public function delete($comment)
	{
		$row = Comment::findOrFail($comment);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])], 500);
		}

		return response()->json(null, 204);
	}
}
