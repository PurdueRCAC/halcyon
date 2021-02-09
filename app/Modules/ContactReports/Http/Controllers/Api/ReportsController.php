<?php

namespace App\Modules\ContactReports\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Reportresource;
use App\Modules\ContactReports\Models\User;
use App\Modules\ContactReports\Http\Resources\ReportResource as ApiReportResource;
use App\Modules\ContactReports\Http\Resources\ReportResourceCollection;
use Carbon\Carbon;

/**
 * Contact Reports
 *
 * @apiUri    /api/contactreports
 */
class ReportsController extends Controller
{
	/**
	 * Display a listing of contact reports
	 *
	 * @apiMethod GET
	 * @apiUri    /api/contactreports
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
	 * 		"name":          "type",
	 * 		"description":   "Contact Report type ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
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
	 * @param  Request $request
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

		if ($filters['search'])
		{
			$query->where($cr . '.report', 'like', '%' . $filters['search'] . '%');
		}

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

		if ($filters['id'])
		{
			$query->where($cr . '.id', '=', $filters['id']);
		}

		if ($filters['people'])
		{
			$filters['people'] = explode(',', $filters['people']);

			$cru = (new User)->getTable();

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
		$query->select($cr . '.*');

		$rows = $query
			->orderBy($cr . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ReportResourceCollection($rows);
	}

	/**
	 * Create a contact report
	 *
	 * @apiMethod POST
	 * @apiUri    /api/contactreports
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimecontact",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the contact",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of the associated group",
	 * 		"type":          "integer",
	 * 		"required":      false,
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
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$now = new Carbon();

		$request->validate([
			'report' => 'required|string',
			'datetimecontact' => 'required|date|before_or_equal:' . $now->toDateTimeString(),
			'userid' => 'nullable|integer',
			'groupid' => 'nullable|integer',
			'datetimegroupid' => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
			'notice' => 'nullable|integer',
		]);

		$row = new Report();
		$row->datetimecontact = $request->input('datetimecontact');
		$row->report = $request->input('report');
		$row->userid = $request->input('userid', auth()->user() ? auth()->user()->id : 0);
		$row->groupid = $request->input('groupid', 0);
		$row->notice = $request->input('notice', 23);

		/*if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row->datetimecontact))
		{
			return response()->json(['message' => __METHOD__ . '(): Invalid value for field `contactdate`'], 409);
		}

		if ($row->datetimecontact > $now)
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

		//$row->stemmedreport = $row->generateStemmedReport();

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		$errors = array();

		if ($users = $request->input('people'))
		{
			foreach ((array)$users as $user)
			{
				$u = new User;
				$u->contactreportid = $row->id;
				$u->userid = $user;

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
	 * @apiUri    /api/contactreports/preview
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "Unformatted text",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
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
	 * @apiUri    /api/contactreports/{id}
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
		$row = Report::findOrFail((int)$id);

		return new ApiReportResource($row);
	}

	/**
	 * Update a contact report
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/contactreports/{id}
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of the associated group",
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
			'datetimecontact' => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
			'groupid' => 'nullable|integer',
			'notice' => 'nullable|integer',
			//'datetimegroupid' => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
		]);

		$row = Report::findOrFail($id);
		//$row->fill($request->all());
		$row->datetimecontact = $request->input('datetimecontact', $row->datetimecontact);
		$row->report = $request->input('report', $row->report);
		$row->userid = $request->input('userid', $row->userid);
		$row->groupid = $request->input('groupid', $row->groupid);
		$row->notice = $request->input('notice', $row->notice);

		/*if ($row->datetimecontact != $row->getOriginal('datetimecontact'))
		{
			if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row->datetimecontact))
			{
				return response()->json(['message' => 'Invalid value for field `datetimecontact`'], 415);
			}

			if ($row->datetimecontact > $now)
			{
				return response()->json(['message' => '`datetimecontact` cannot be in the future'], 415);
			}
		}

		if ($row->report != $row->getOriginal('report'))
		{
			if (!$row->report)
			{
				return response()->json(['message' =>  '`report` cannot be empty'], 415);
			}
		}*/

		if ($row->groupid != $row->getOriginal('groupid'))
		{
			if ($row->groupid && !$row->group)
			{
				return response()->json(['message' => 'Group not found for provided `groupid`'], 409);
			}

			$row->datetimegroupid = $now->toDateTimeString();
		}

		//$row->stemmedreport = $row->generateStemmedReport();

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
				$rr = new User;
				$rr->contactreportid = $row->id;
				$rr->userid = $r;

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
	 * @apiUri    /api/contactreports/{id}
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
		$row = Report::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
