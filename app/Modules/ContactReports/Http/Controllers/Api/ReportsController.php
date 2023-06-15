<?php

namespace App\Modules\ContactReports\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Reportresource;
use App\Modules\ContactReports\Models\User as ContactUser;
use App\Modules\ContactReports\Http\Resources\ReportResource as ApiReportResource;
use App\Modules\ContactReports\Http\Resources\ReportResourceCollection;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

/**
 * Contact Reports
 *
 * @apiUri    /contactreports
 */
class ReportsController extends Controller
{
	/**
	 * Display a listing of contact reports
	 *
	 * @apiMethod GET
	 * @apiUri    /contactreports
	 * @apiAuthorization  true
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
	 * 		"name":          "type",
	 * 		"description":   "Contact Report type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "group",
	 * 		"description":   "Filter by group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "start",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) for records on or after that date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date",
	 * 			"example":   "2021-01-30"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "stop",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) for records before that date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date",
	 * 			"example":   "2021-01-30"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "people",
	 * 		"description":   "Filter by people associated with reports. Comma-separated list of usernames, emails, or user IDs",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "person1,person2,person3"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resource",
	 * 		"description":   "Filter by resources associated with reports. Comma-separated list of resource IDs",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "1,2,3"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tag",
	 * 		"description":   "Filter by tags associated with reports. Comma-separated list of tags",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "tag1,tag2,tag3"
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
	 * 		"name":          "notice",
	 * 		"description":   "Filter by notice value",
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
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"report",
	 * 				"datetimecreated"
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
	 * @param  Request $request
	 * @return ReportResourceCollection
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
			'tag'       => null,
			'type'      => '*',
			'notice'    => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Report::$orderBy,
			'order_dir' => Report::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'report', 'datetimecreated']))
		{
			$filters['order'] = Report::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Report::$orderDir;
		}

		$query = Report::query();

		$cr = (new Report)->getTable();

		if ($filters['id'])
		{
			$query->where($cr . '.id', '=', $filters['id']);
		}
		else
		{
			if ($filters['notice'] != '*')
			{
				$query->where($cr . '.notice', '=', $filters['notice']);
			}

			if ($filters['type'] != '*')
			{
				$query->where($cr . '.contactreporttypeid', '=', $filters['type']);
			}

			if ($filters['group'])
			{
				$filters['group'] = explode(',', $filters['group']);

				$query->whereIn($cr . '.groupid', $filters['group']);
			}

			if ($filters['start'])
			{
				$start = Carbon::parse($filters['start']);
				$query->where($cr . '.datetimecontact', '>=', $start->toDateTimeString());
			}

			if ($filters['stop'])
			{
				$stop = Carbon::parse($filters['stop']);
				$query->where($cr . '.datetimecontact', '<=', $stop->toDateTimeString());
			}

			if ($filters['people'])
			{
				$filters['people'] = explode(',', $filters['people']);
				foreach ($filters['people'] as $k => $person)
				{
					if (!is_numeric($person))
					{
						$user = User::findByUsername($person);
						if ($user && $user->id)
						{
							$filters['people'][$k] = $user->id;
						}
					}
				}

				$cru = (new ContactUser)->getTable();

				$query->join($cru, $cru . '.contactreportid', $cr . '.id');
				$query->where(function ($where) use ($filters, $cru, $cr)
					{
						$where->whereIn($cru . '.userid', $filters['people'])
							->orWhereIn($cr . '.userid', $filters['people']);
					});
			}

			if ($filters['resource'])
			{
				$filters['resource'] = explode(',', $filters['resource']);

				$crr = (new Reportresource)->getTable();

				$query->join($crr, $crr . '.contactreportid', $cr . '.id')
					->whereIn($crr . '.resourceid', $filters['resource']);
			}

			if ($filters['tag'])
			{
				$filters['tag'] = explode(',', $filters['tag']);

				$query->withTag($filters['tag']);
			}

			if ($filters['search'])
			{
				if (is_numeric($filters['search']))
				{
					$query->where($cr . '.id', '=', (int)$filters['search']);
				}
				else
				{
					// Trim extra garbage
					$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $filters['search']);

					// Calculate stem for the word
					$keywords = array();
					$stem = PorterStemmer::stem($keyword);
					$stem = substr($stem, 0, 1) . $stem;

					$keywords[] = $stem;

					// Select score
					$sql  = "(MATCH(" . $cr . ".stemmedreport) AGAINST ('+";
					$sql .= $keywords[0];
					for ($i=1; $i<count($keywords); $i++)
					{
						$sql .= " +" . $keywords[$i];
					}
					$sql .= "') * 10 + 2 * (1 / (DATEDIFF(NOW(), " . $cr . ".datetimecontact) + 1))) AS score";

					$query->select(['*', DB::raw($sql)]);

					// Where match
					$sql  = "MATCH(" . $cr . ".stemmedreport) AGAINST ('+";
					$sql .= $keywords[0];
					for ($i=1; $i<count($keywords); $i++)
					{
						$sql .= " +" . $keywords[$i];
					}
					$sql .= "' IN BOOLEAN MODE)";

					$query->whereRaw($sql)
						->orderBy('score', 'desc');

					//$query->where('report', 'like', '%' . $filters['search'] . '%');

					/*if (empty($filters['tag']))
					{
						$filters['tag'] = preg_replace('/\s+/', ',', $filters['search']);
					}*/
				}
			}
			else
			{
				$query->select($cr . '.*');
			}
		}

		$rows = $query
			->orderBy($cr . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ReportResourceCollection($rows);
	}

	/**
	 * Create a contact report
	 *
	 * @apiMethod POST
	 * @apiUri    /contactreports
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimecontact",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the contact",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date",
	 * 			"example":   "2021-01-30"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of the associated group",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimegroupid",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the contact",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date",
	 * 			"example":   "2021-01-30"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "ID of the user creating the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "contactreporttypeid",
	 * 		"description":   "Type ID for the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "report",
	 * 		"description":   "The report text",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 5830,
	 * 						"groupid": 142,
	 * 						"userid": 61344,
	 * 						"report": "Example text",
	 * 						"stemmedreport": "eexample ttext",
	 * 						"datetimecontact": "2021-09-03T04:00:00.000000Z",
	 * 						"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 						"notice": 23,
	 * 						"datetimegroupid": null,
	 * 						"contactreporttypeid": 3,
	 * 						"formatteddate": "September 3, 2021 4:01pm",
	 * 						"formattedreport": "<p>Example text.</p>",
	 * 						"comments": [],
	 * 						"subscribed": 1,
	 * 						"subscribedcommentid": 0,
	 * 						"type": {
	 * 							"id": 3,
	 * 							"name": "Personal Meeting",
	 * 							"timeperiodid": 1,
	 * 							"timeperiodcount": 7,
	 * 							"timeperiodlimit": 14,
	 * 							"waitperiodid": 0,
	 * 							"waitperiodcount": 0
	 * 						},
	 * 						"username": "John Doe",
	 * 						"users": [
	 * 							{
	 * 								"id": 9342,
	 * 								"contactreportid": 5830,
	 * 								"userid": 56907,
	 * 								"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 								"datetimelastnotify": null,
	 * 								"name": "Gerhard Klimeck"
	 * 							},
	 * 							{
	 * 								"id": 9343,
	 * 								"contactreportid": 5830,
	 * 								"userid": 81163,
	 * 								"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 								"datetimelastnotify": null,
	 * 								"name": "Erik S Gough"
	 * 							}
	 * 						],
	 * 						"groupname": "Lorem Ipsum",
	 * 						"resources": [
	 * 							{
	 * 								"id": 106819,
	 * 								"contactreportid": 5830,
	 * 								"resourceid": 110,
	 * 								"name": "Geddes"
	 * 							},
	 * 							{
	 * 								"id": 106820,
	 * 								"contactreportid": 5830,
	 * 								"resourceid": 92,
	 * 								"name": "Gilbreth"
	 * 							}
	 * 						],
	 * 						"tags": [],
	 * 						"age": 1200675,
	 * 						"api": "https://example.org/api/contactreports/5830",
	 * 						"url": "https://example.org/contactreports/5830"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse|ApiReportResource
	 */
	public function create(Request $request)
	{
		$now = Carbon::now();

		$rules = [
			'report'              => 'required|string',
			'datetimecontact'     => 'required|date|before_or_equal:' . $now->toDateTimeString(),
			'userid'              => 'nullable|integer',
			'groupid'             => 'nullable|integer',
			'datetimegroupid'     => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
			'notice'              => 'nullable|integer',
			'contactreporttypeid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Report();
		$row->datetimecontact = $request->input('datetimecontact');
		$row->report = $request->input('report');
		$row->userid = $request->input('userid');
		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}
		if ($request->has('contactreporttypeid') && $request->input('contactreporttypeid') >= 0)
		{
			$row->contactreporttypeid = $request->input('contactreporttypeid');
		}
		$row->groupid = $request->input('groupid', 0);
		$row->notice = $request->input('notice', 23);

		/*if ($row->datetimecontact > $now)
		{
			return response()->json(['message' => __METHOD__ . '(): `contactdate` cannot be in the future'], 409);
		}*/

		if ($row->groupid)
		{
			if (!$row->group)
			{
				return response()->json(['message' => __METHOD__ . '(): Group not found for provided `groupid`'], 409);
			}

			$row->datetimegroupid = $now->toDateTimeString();
		}

		$row->datetimecreated = $now->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$errors = array();

		if ($users = $request->input('users'))
		{
			foreach ((array)$users as $user)
			{
				if (!is_numeric($user))
				{
					$usr = User::createFromUsername($user);
				}
				else
				{
					$usr = User::find($user);
				}

				if (!$usr || !$usr->id)
				{
					continue;
				}

				$u = new ContactUser;
				$u->contactreportid = $row->id;
				$u->userid = $usr->id;

				if (!$u->save())
				{
					$errors[] = __METHOD__ . '(): Failed to create `contactreportuser` entry for userid #' . $user;
				}
			}
		}

		if ($resources = $request->input('resources'))
		{
			foreach ((array)$resources as $resource)
			{
				$rr = new Reportresource;
				$rr->contactreportid = $row->id;
				$rr->resourceid = $resource;

				if (!$rr->save())
				{
					$errors[] = __METHOD__ . '(): Failed to create `contactreportresources` entry for resourceid #' . $resource;
				}
			}
		}

		$row->errors = $errors;

		return new ApiReportResource($row);
	}

	/**
	 * Parse submitted text to see the final result
	 *
	 * @apiMethod POST
	 * @apiUri    /contactreports/preview
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "Unformatted text",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  ApiReportResource
	 */
	public function preview(Request $request)
	{
		$row = new Report;
		$row->body = $request->input('body');

		$row->datetimenews = Carbon::now()->toDateTimeString();
		if ($date = $request->input('datetimenews'))
		{
			$row->datetimenews = $date;
		}

		return new ApiReportResource($row);
	}

	/**
	 * Retrieve a contact report
	 *
	 * @apiMethod GET
	 * @apiUri    /contactreports/{id}
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
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 5830,
	 * 						"groupid": 142,
	 * 						"userid": 61344,
	 * 						"report": "Example text",
	 * 						"stemmedreport": "eexample ttext",
	 * 						"datetimecontact": "2021-09-03T04:00:00.000000Z",
	 * 						"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 						"notice": 23,
	 * 						"datetimegroupid": null,
	 * 						"contactreporttypeid": 3,
	 * 						"formatteddate": "September 3, 2021 4:01pm",
	 * 						"formattedreport": "<p>Example text.</p>",
	 * 						"comments": [],
	 * 						"subscribed": 1,
	 * 						"subscribedcommentid": 0,
	 * 						"type": {
	 * 							"id": 3,
	 * 							"name": "Personal Meeting",
	 * 							"timeperiodid": 1,
	 * 							"timeperiodcount": 7,
	 * 							"timeperiodlimit": 14,
	 * 							"waitperiodid": 0,
	 * 							"waitperiodcount": 0
	 * 						},
	 * 						"username": "John Doe",
	 * 						"users": [
	 * 							{
	 * 								"id": 9342,
	 * 								"contactreportid": 5830,
	 * 								"userid": 56907,
	 * 								"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 								"datetimelastnotify": null,
	 * 								"name": "Gerhard Klimeck"
	 * 							},
	 * 							{
	 * 								"id": 9343,
	 * 								"contactreportid": 5830,
	 * 								"userid": 81163,
	 * 								"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 								"datetimelastnotify": null,
	 * 								"name": "Erik S Gough"
	 * 							}
	 * 						],
	 * 						"groupname": "Lorem Ipsum",
	 * 						"resources": [
	 * 							{
	 * 								"id": 106819,
	 * 								"contactreportid": 5830,
	 * 								"resourceid": 110,
	 * 								"name": "Geddes"
	 * 							},
	 * 							{
	 * 								"id": 106820,
	 * 								"contactreportid": 5830,
	 * 								"resourceid": 92,
	 * 								"name": "Gilbreth"
	 * 							}
	 * 						],
	 * 						"tags": [],
	 * 						"age": 1200675,
	 * 						"api": "https://example.org/api/contactreports/5830",
	 * 						"url": "https://example.org/contactreports/5830"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return ApiReportResource
	 */
	public function read($id)
	{
		$row = Report::findOrFail((int)$id);

		return new ApiReportResource($row);
	}

	/**
	 * Update a contact report
	 *
	 * @apiMethod PUT
	 * @apiUri    /contactreports/{id}
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
	 * 		"name":          "datetimecontact",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the contact",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of the associated group",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "report",
	 * 		"description":   "The report text",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "contactreporttypeid",
	 * 		"description":   "Type ID for the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 5830,
	 * 						"groupid": 142,
	 * 						"userid": 61344,
	 * 						"report": "Example text",
	 * 						"stemmedreport": "eexample ttext",
	 * 						"datetimecontact": "2021-09-03T04:00:00.000000Z",
	 * 						"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 						"notice": 23,
	 * 						"datetimegroupid": null,
	 * 						"contactreporttypeid": 3,
	 * 						"formatteddate": "September 3, 2021 4:01pm",
	 * 						"formattedreport": "<p>Example text.</p>",
	 * 						"comments": [],
	 * 						"subscribed": 1,
	 * 						"subscribedcommentid": 0,
	 * 						"type": {
	 * 							"id": 3,
	 * 							"name": "Personal Meeting",
	 * 							"timeperiodid": 1,
	 * 							"timeperiodcount": 7,
	 * 							"timeperiodlimit": 14,
	 * 							"waitperiodid": 0,
	 * 							"waitperiodcount": 0
	 * 						},
	 * 						"username": "John Doe",
	 * 						"users": [
	 * 							{
	 * 								"id": 9342,
	 * 								"contactreportid": 5830,
	 * 								"userid": 56907,
	 * 								"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 								"datetimelastnotify": null,
	 * 								"name": "Gerhard Klimeck"
	 * 							},
	 * 							{
	 * 								"id": 9343,
	 * 								"contactreportid": 5830,
	 * 								"userid": 81163,
	 * 								"datetimecreated": "2021-09-03T20:01:44.000000Z",
	 * 								"datetimelastnotify": null,
	 * 								"name": "Erik S Gough"
	 * 							}
	 * 						],
	 * 						"groupname": "Lorem Ipsum",
	 * 						"resources": [
	 * 							{
	 * 								"id": 106819,
	 * 								"contactreportid": 5830,
	 * 								"resourceid": 110,
	 * 								"name": "Geddes"
	 * 							},
	 * 							{
	 * 								"id": 106820,
	 * 								"contactreportid": 5830,
	 * 								"resourceid": 92,
	 * 								"name": "Gilbreth"
	 * 							}
	 * 						],
	 * 						"tags": [],
	 * 						"age": 1200675,
	 * 						"api": "https://example.org/api/contactreports/5830",
	 * 						"url": "https://example.org/contactreports/5830"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  JsonResponse|ApiReportResource
	 */
	public function update(Request $request, $id)
	{
		$now = Carbon::now();

		$rules = [
			'report'              => 'nullable|string',
			'datetimecontact'     => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
			'groupid'             => 'nullable|integer',
			'notice'              => 'nullable|integer',
			'contactreporttypeid' => 'nullable|integer',
			//'datetimegroupid'     => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Report::findOrFail($id);
		$row->datetimecontact = $request->input('datetimecontact', $row->datetimecontact);
		$row->report = $request->input('report', $row->report);
		$row->userid = $request->input('userid', $row->userid);
		$row->groupid = $request->input('groupid', $row->groupid);
		$row->notice = $request->input('notice', $row->notice);
		if ($request->has('contactreporttypeid') && $request->input('contactreporttypeid') >= 0)
		{
			$row->contactreporttypeid = $request->input('contactreporttypeid');
		}

		if ($request->has('datetimecontact'))
		{
			if ($row->datetimecontact > $now)
			{
				return response()->json(['message' => '`datetimecontact` cannot be in the future'], 415);
			}
		}

		if ($request->has('report'))
		{
			if (!$row->report)
			{
				return response()->json(['message' =>  '`report` cannot be empty'], 415);
			}
		}

		if ($row->groupid != $row->getOriginal('groupid'))
		{
			if ($row->groupid && !$row->group)
			{
				return response()->json(['message' => 'Group not found for provided `groupid`'], 409);
			}

			$row->datetimegroupid = $now->toDateTimeString();
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		$errors = array();

		if ($resources = $request->input('resources'))
		{
			$resources = (array)$resources;

			// Fetch current list of resources
			$prior = $row->resources;

			// Remove and add resource-contactreport mappings
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
					$errors[] = 'Failed to delete `contactreportresources` entry #' . $r;
				}
			}

			// Ensure unique-ness
			$addresources = array_unique($addresources);

			foreach ($addresources as $r)
			{
				$rr = new Reportresource;
				$rr->contactreportid = $row->id;
				$rr->resourceid = $r;

				if (!$rr->save())
				{
					$errors[] = 'Failed to create `contactreportresources` entry for resourceid #' . $r;
				}
			}
		}

		if ($people = $request->input('users'))
		{
			$people = (array)$people;

			// Fetch current list of resources
			$prior = $row->users;

			// Remove and add resource-contactreport mappings
			// First calculate diff
			$addusers = array();
			$deleteusers = array();

			foreach ($prior as $r)
			{
				$found = false;

				foreach ($people as $r2)
				{
					if ($r2 == $r->userid)
					{
						$found = true;
					}
				}

				if (!$found)
				{
					array_push($deleteusers, $r);
				}
			}

			foreach ($people as $r)
			{
				$found = false;

				foreach ($prior as $r2)
				{
					if ($r2->userid == $r)
					{
						$found = true;
					}
				}

				if (!$found)
				{
					array_push($addusers, $r);
				}
			}

			foreach ($deleteusers as $r)
			{
				if (!$r->delete())
				{
					$errors[] = 'Failed to delete `contactreportresources` entry #' . $r;
				}
			}

			// Ensure unique-ness
			$addusers = array_unique($addusers);

			foreach ($addusers as $r)
			{
				if (!is_numeric($r))
				{
					$usr = User::createFromUsername($r);
				}
				else
				{
					$usr = User::find($r);
				}

				if (!$usr || !$usr->id)
				{
					continue;
				}

				$rr = new ContactUser;
				$rr->contactreportid = $row->id;
				$rr->userid = $usr->id;

				if (!$rr->save())
				{
					$errors[] = 'Failed to create `contactreportuser` entry for userid #' . $r;
				}
			}
		}

		$row = $row->fresh();
		$row->errors = $errors;

		return new ApiReportResource($row);
	}

	/**
	 * Delete a contact report
	 *
	 * @apiMethod DELETE
	 * @apiUri    /contactreports/{id}
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
	 * @param   int  $id
	 * @return  JsonResponse
	 */
	public function delete($id)
	{
		$row = Report::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
