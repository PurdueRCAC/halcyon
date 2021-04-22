<?php

namespace App\Modules\ContactReports\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Comment;
use App\Modules\ContactReports\Http\Resources\CommentResource;
use App\Modules\ContactReports\Http\Resources\CommentResourceCollection;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

/**
 * Comments
 *
 * @apiUri    /api/contactreports/comments
 */
class CommentsController extends Controller
{
	/**
	 * Display a listing of contact reports comments
	 *
	 * @apiMethod GET
	 * @apiUri    /api/contactreports/comments
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of contact report",
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
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'contactreportid' => 0,
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

		$query = Comment::query();
		
		if ($filters['contactreportid'])
		{
			$query->where('contactreportid', $filters['contactreportid']);
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				// Trim extra garbage
				$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $filters['search']);

				// Calculate stem for the word
				$keywords = array();
				$stem = PorterStemmer::Stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;
				$keywords[] = $stem;

				$match = implode(' +', $keywords);
				$match = trim($match);

				// Select score
				$sql = "(MATCH(stemmedcomment) AGAINST ('" . $match . "') * 10 + 2 * (1 / (DATEDIFF(NOW(), datetimecreated) + 1))) AS score";

				$query->select(['*', DB::raw($sql)]);

				// Where match
				$sql = "MATCH(stemmedcomment) AGAINST ('" . $match . "' IN BOOLEAN MODE)";

				$query->whereRaw($sql)
					->orderBy('score', 'desc');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new CommentResourceCollection($rows);
	}

	/**
	 * Create a contact report comment
	 *
	 * @apiMethod POST
	 * @apiUri    /api/contactreports/comments
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
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of the contact report",
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
			'comment' => 'nullable|string|max:8096',
			'contactreportid' => 'required|integer',
		]);

		$row = new Comment($request->all());
		if (!$row->comment)
		{
			$row->comment = '';
		}
		$row->userid = auth()->user() ? auth()->user()->id : 0;

		if (!$row->report)
		{
			return response()->json(['message' => __METHOD__ . '(): Invalid contactreport ID'], 409);
		}

		// Set notice state
		$row->notice = 0;

		if ($row->comment != '')
		{
			$row->notice = 22;
		}

		$row->datetimecreated = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new CommentResource($row);
	}

	/**
	 * Retrieve a contact report comment
	 *
	 * @apiMethod GET
	 * @apiUri    /api/contactreports/comments/{id}
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
	public function read($id)
	{
		$item = Comment::findOrFail((int)$id);

		return new CommentResource($item);
	}

	/**
	 * Update a contact report comment
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/contactreports/comments/{id}
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
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of the contact report",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'comment' => 'nullable|string',
			'contactreportid' => 'nullable|integer',
			'userid' => 'nullable|integer',
			'notice' => 'nullable|integer',
		]);

		$data = $request->all();

		if (isset($data['datetimecreated']))
		{
			unset($data['datetimecreated']);
		}

		if (!auth()->user() || !auth()->user()->can('admin contactreports'))
		{
			unset($data['userid']);
		}

		$row = Comment::findOrFail($id);
		$row->fill($data);

		if ($request->has('contactreportid'))
		{
			if (!$row->report)
			{
				return response()->json(['message' => __METHOD__ . '(): Invalid contactreport ID'], 409);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		return new CommentResource($row);
	}

	/**
	 * Delete a contact report comment
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/contactreports/comments/{id}
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
	public function delete($id)
	{
		$row = Comment::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])], 500);
		}

		return response()->json(null, 204);
	}
}
