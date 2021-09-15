<?php

namespace App\Modules\Courses\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Courses\Events\AccountLookup;
use App\Modules\Courses\Events\AccountInstructorLookup;
use App\Modules\Courses\Events\AccountEnrollment;
use App\Modules\Courses\Events\CourseEnrollment;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Models\Asset;
use App\Modules\Courses\Http\Resources\AccountResource;
use App\Modules\Courses\Http\Resources\AccountResourceCollection;
use App\Modules\Users\Models\User;
use App\Modules\Users\Events\UserLookup;
use Carbon\Carbon;

/**
 * Accounts
 *
 * @apiUri    /api/courses
 */
class AccountsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /courses
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "userid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "deptartment",
	 * 		"description":   "Department code",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 4,
	 *			"example":   "STAT"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "start",
	 * 		"description":   "Filter entries scheduled on or after this date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "stop",
	 * 		"description":   "Filter entries scheduled to end before this date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
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
	 * 			"default":   25
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
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"owneruserid",
	 * 				"unixgroup",
	 * 				"unixid",
	 * 				"deptnumber"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
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
		$filters = array(
			'search'     => $request->input('search', ''),
			'userid'     => $request->input('userid', 0),
			'groupid'    => $request->input('groupid', 0),
			'resourceid' => $request->input('resourceid', 0),
			'department' => $request->input('department'),
			'crn'        => $request->input('crn'),
			'semester'   => $request->input('semester'),
			'notice'     => $request->input('notice'),
			'starts'     => $request->input('starts'),
			'stops'      => $request->input('stops'),
			// Paging
			'limit'      => $request->input('limit', config('list_limit', 20)),
			'page'       => $request->input('page', 1),
			// Sorting
			'order'      => $request->input('order', Account::$orderBy),
			'order_dir'  => $request->input('order_dir', Account::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Account::$orderDir;
		}

		$query = Account::query()
			->withTrashed()
			->whereIsActive();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where('classname', 'like', '%' . $filters['search'] . '%');
		}

		if (!is_null($filters['notice']))
		{
			$query->where('notice', '=', $filters['notice']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		if ($filters['resourceid'])
		{
			$query->where('resourceid', '=', $filters['resourceid']);
		}

		if ($filters['department'])
		{
			$query->where('department', '=', $filters['department']);
		}

		if ($filters['crn'])
		{
			$query->where('crn', '=', $filters['crn']);
		}

		if ($filters['semester'])
		{
			$query->where('semester', '=', $filters['semester']);
		}

		if ($filters['starts'])
		{
			$query->where('datetimestart', '>=', $filters['starts']);
		}

		if ($filters['stops'])
		{
			$query->where('datetimestop', '<', $filters['stops']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new AccountResourcecollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /courses
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "crn",
	 * 		"description":   "Course CRN",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 8,
	 *			"example":   "5d8293ce"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "classname",
	 * 		"description":   "Class name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 255,
	 * 			"example":   "Intro To Statistics"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "coursenumber",
	 * 		"description":   "Course number",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 8,
	 *			"example":   "39100A"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptartment",
	 * 		"description":   "Department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 4,
	 *			"example":   "STAT"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Start date of the entry",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "End date of the entry",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'crn' => 'nullable|max:8',
			'department' => 'nullable|string|max:4',
			'coursenumber' => 'nullable|string|max:8',
			'classname' => 'required|string|max:255',
			'resourceid' => 'required|integer|min:1',
			'groupid' => 'nullable|integer|min:1',
			'userid' => 'nullable|integer|min:1',
			'datetimestart' => 'required|date',
			'datetimestop' => 'required|date',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$type = $request->input('type');

		$row = new Account;
		$row->crn = $request->input('crn');
		$row->department = $request->input('department', '');
		$row->coursenumber = $request->input('coursenumber', '');
		$row->classname = $request->input('classname');
		$row->resourceid = $request->input('resourceid');
		$row->groupid = $request->input('groupid', 0);
		$row->userid = $request->input('userid', 0);
		$row->reference = $request->input('reference', '');
		$row->semester = $request->input('semester', '');
		$row->datetimestart = $request->input('datetimestart', $request->input('start'));
		$row->datetimestop = $request->input('datetimestop', $request->input('stop'));
		
		if ($request->has('classid'))
		{
			$row->classid = $request->input('classid');
		}

		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

		// Swith for class vs workshop
		if ($type == 'workshop' || strtolower($row->semester) == 'workshop')
		{
			if ($row->classname == '')
			{
				return response()->json(['message' => trans('courses::courses.invalid.name')], 415);
			}
			if ($row->datetimestart == '')
			{
				return response()->json(['message' => trans('courses::courses.invalid.start date')], 415);
			}
			if ($row->datetimestop == '')
			{
				return response()->json(['message' => trans('courses::courses.invalid.end date')], 415);
			}
			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-1 day')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+1 day')->toDateTimeString();
			$row->crn = uniqid();
			$row->crn = substr($row->crn, 0, 8); 
			$row->semester = 'Workshop';
			$row->reference = $row->semester;
			$row->department = '';
			$row->coursenumber = '';
		}
		else
		{
			// Check to see if CRN is already in the system.
			// TODO: are CRNs unique?
			// TODO: fine tune date range. Does requested date range overlap with another?
			$exist = Account::query()
				->where('crn', '=', $row->crn)
				->where('semester', '=', $row->semester)
				->withTrashed()
				->whereIsActive()
				->get()
				->first();

			if ($exist)
			{
				return response()->json(['message' => trans('courses::courses.error.duplicate crn')], 415);
			}

			// Fetch information about class from input.
			event($event = new AccountLookup($row));

			$row = $event->account;

			if (!$row->crn)
			{
				// Invalid CRN/classID provided
				return response()->json(['message' => trans('courses::courses.error.invalid class')], 500);
			}

			// If this is a CRN course, add/subtract from time.
			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-3 days')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+7 days')->toDateTimeString();
			$row->notice = 1;
		}

		unset($row->classid);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($users = $request->input('users'))
		{
			foreach ($users as $user)
			{
				$member = new Member;
				$member->userid = $user;
				$member->classaccountid = $row->id;
				$member->datetimestart = $row->start;
				$member->datetimestop = $row->stop;

				if (!$member->save())
				{
					return response()->json(['message' => trans('courses::courses.error.entry failed for user', ['name' => 'ID #' . $user])], 500);
				}
			}
		}

		return new AccountResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /courses/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return AccountResource
	 */
	public function read($id)
	{
		$row = Account::findOrFail($id);

		return new AccountResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /courses/{id}
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
	 * 		"name":          "crn",
	 * 		"description":   "Course CRN",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 8,
	 *			"example":   "5d8293ce"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "classname",
	 * 		"description":   "Class name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 255,
	 * 			"example":   "Intro To Statistics"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "coursenumber",
	 * 		"description":   "Course number",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 8,
	 *			"example":   "39100A"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptartment",
	 * 		"description":   "Department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 *			"maxLength": 4,
	 *			"example":   "STAT"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Start date of the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "End date of the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  AccountResource
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'crn' => 'nullable|string|max:8',
			'department' => 'nullable|string|max:4',
			'coursenumber' => 'nullable|string|max:8',
			'classname' => 'nullable|string|max:255',
			'resourceid' => 'nullable|integer|min:1',
			'groupid' => 'nullable|integer|min:1',
			'userid' => 'nullable|integer|min:1',
			'datetimestart' => 'nullable|date',
			'datetimestop' => 'nullable|date',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Account::findOrFail($id);

		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if ($row->datetimestart >= $row->datetimestop)
		{
			return response()->json(['message' => trans('courses::courses.error.invalid dates')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new AccountResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /courses/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Account::findOrFail($id);

		if (!auth()->user()->can('delete courses')
		 && auth()->user()->id != $row->userid)
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 403);
		}

		if (!$row->isTrashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}

	/**
	 * Sync users with account info
	 *
	 * @apiMethod GET
	 * @apiUri    /courses/sync
	 * @return  Response
	 */
	public function sync()
	{
		Artisan::call('courses:sync', [
			'--debug' => 1
		]);

		$output = Artisan::output();

		$data = explode("\n", $output);

		return response()->json($data);
	}

	/**
	 * Lookup enrollments for a class
	 * 
	 * @apiMethod GET
	 * @apiUri    /courses/enrollments
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "crn",
	 * 		"description":   "Course CRN",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "classid",
	 * 		"description":   "Course ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function enrollments(Request $request)
	{
		$account = new Account();
		$account->crn = $request->input('crn');
		$account->classid = $request->input('classid');

		$data = array('enrollments' => []);

		event($e = new AccountEnrollment($account));

		$data['enrollments'] = $e->enrollments;

		return response()->json($data);
	}
}
