<?php

namespace App\Modules\Issues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Issueresource;
use App\Modules\Issues\Http\Resources\IssueResource as ApiIssueResource;
use App\Modules\Issues\Http\Resources\IssueResourceCollection;
use Carbon\Carbon;

/**
 * Contact Issues
 *
 * @apiUri    /api/issues
 */
class IssuesController extends Controller
{
	/**
	 * Display a listing of issues
	 *
	 * @apiMethod GET
	 * @apiUri    /api/issues
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
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
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
			'search'    => null,
			'id'        => null,
			'group'     => null,
			'start'     => null,
			'stop'      => null,
			'people'    => null,
			'resource'  => null,
			'issuetodoid' => 0,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Issue::$orderBy,
			'order_dir' => Issue::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'report', 'datetimecreated']))
		{
			$filters['order'] = Issue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Issue::$orderDir;
		}

		$query = Issue::query()
			->withTrashed()
			->whereIsActive();

		$cr = (new Issue)->getTable();

		if ($filters['search'])
		{
			//$query->where($cr . '.report', 'like', '%' . $filters['search'] . '%');

			$keywords = explode(' ', $filters['search']);

			$from_sql = array();
			foreach ($keywords as $keyword)
			{
				// Trim extra garbage
				$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $keyword);

				// Calculate stem for the word
				$stem = PorterStemmer::Stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;

				$from_sql[] = "+" . $stem;
			}

			$query->select('*', DB::raw("(MATCH(stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), $n.datetimecreated)) + 1))) AS score"));
			$query->whereRaw("MATCH(stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
			$query->orderBy('score', 'desc');
		}

		if ($filters['issuetodoid'] >= 0)
		{
			$query->where($cr . '.issuetodoid', '=', $filters['issuetodoid']);
		}

		if ($filters['start'])
		{
			$query->where($cr . '.datetimecreated', '>=', $filters['start'] . ' 00:00:00');
		}

		if ($filters['stop'])
		{
			$query->where($cr . '.datetimecreated', '<=', $filters['stop'] . ' 23:59:59');
		}

		if ($filters['id'])
		{
			$query->where($cr . '.id', '=', $filters['id']);
		}

		if ($filters['resource'])
		{
			$filters['resource'] = explode(',', $filters['resource']);

			$crr = (new Issueresource)->getTable();

			$query->join($crr, $crr . '.issueid', $cr . '.id')
				->whereIn($crr . '.resourceid', $filters['resource']);
		}
		$query->select($cr . '.*');

		$rows = $query
			->orderBy($cr . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new IssueResourceCollection($rows);
	}

	/**
	 * Create a new issue
	 *
	 * @apiMethod POST
	 * @apiUri    /api/issues
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimecreated",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the issue",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "ID of the user creating the entry",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "issuetodo",
	 * 		"description":   "Is this a To-Do item?",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$now = new Carbon();

		$request->validate([
			'report' => 'required|string',
			'datetimecreated' => 'nullable|date',
			'userid' => 'nullable|integer',
			'issuetodoid' => 'nullable|integer',
		]);

		$row = new Issue();
		$row->datetimecreated = $request->input('datetimecreated', $now->toDateTimeString());
		$row->report = $request->input('report');
		$row->userid = $request->input('userid', auth()->user() ? auth()->user()->id : 0);
		$row->issuetodoid = $request->input('issuetodoid', 0);

		//$row->stemmedreport = $row->generateStemmedIssue();

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		$errors = array();

		if ($resources = $request->input('resources'))
		{
			foreach ((array)$resources as $resource)
			{
				$rr = new Issueresource;
				$rr->issueid = $row->id;
				$rr->resourceid = $resource;

				if (!$rr->save())
				{
					$errors[] = __METHOD__ . '(): Failed to create `issueresources` entry for resourceid #' . $resource;
				}
			}
		}

		$row->errors = $errors;

		return new ApiIssueResource($row);
	}

	/**
	 * Retrieve an issue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/issues/{id}
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
		$row = Issue::findOrFail((int)$id);

		return new ApiIssueResource($row);
	}

	/**
	 * Update an issue
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/issues/{id}
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
	 * 		"name":          "datetimecreated",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the issue",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "issuetodo",
	 * 		"description":   "Is this a To-Do item?",
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
		$now = new Carbon();

		$request->validate([
			'report' => 'nullable|string',
			'datetimecreated' => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
			'issuetodoid' => 'nullable|integer',
		]);

		$row = Issue::findOrFail($id);
		//$row->fill($request->all());
		$row->datetimecreated = $request->input('datetimecreated', $row->datetimecreated);
		$row->report = $request->input('report', $row->report);
		$row->issuetodoid = $request->input('issuetodoid', $row->issuetodoid);

		if (!$row->report)
		{
			return response()->json(['message' =>  '`report` cannot be empty'], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.update failed')], 500);
		}

		$errors = array();

		if ($resources = $request->input('resources'))
		{
			$resources = (array)$resources;

			// Fetch current list of resources
			$prior = $row->resources;

			// Remove and add resource-issue mappings
			// First calculate diff
			$addresources = array();
			$deleteresources = array();

			foreach ($prior as $r)
			{
				$found = false;

				foreach ($resources as $r2)
				{
					if ($r2 == $r->resourceid)
					{
						$found = true;
					}
				}

				if (!$found)
				{
					array_push($deleteresources, $r);
				}
			}

			foreach ($resources as $r)
			{
				$found = false;

				foreach ($prior as $r2)
				{
					if ($r2->resourceid == $r)
					{
						$found = true;
					}
				}

				if (!$found)
				{
					array_push($addresources, $r);
				}
			}

			foreach ($deleteresources as $r)
			{
				if (!$r->delete())
				{
					$errors[] = 'Failed to delete `issueresources` entry #' . $r;
				}
			}

			// Ensure unique-ness
			$addresources = array_unique($addresources);

			foreach ($addresources as $r)
			{
				$rr = new Issueresource;
				$rr->contactreportid = $row->id;
				$rr->resourceid = $r;

				if (!$rr->save())
				{
					$errors[] = 'Failed to create `issueresources` entry for resourceid #' . $r;
				}
			}
		}

		$row = $row->fresh();
		$row->errors = $errors;

		return new ApiIssueResource($row);
	}

	/**
	 * Delete an issue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/issues/{id}
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
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Issue::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
