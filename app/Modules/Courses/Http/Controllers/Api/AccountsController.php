<?php

namespace App\Modules\Courses\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
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
			'deptnumber' => $request->input('department'),
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

		if ($filters['semester'])
		{
			$query->where('semester', '=', $filters['semester']);
		}

		if ($filters['start'])
		{
			$query->where('.datetimestart', '>=', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('datetimestop', '<', $filters['stop']);
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
				return response()->json(['message' => trans('Required field `classname` is empty')], 415);
			}
			if ($row->datetimestart == '')
			{
				return response()->json(['message' => trans('Required field `start` is empty')], 415);
			}
			if ($row->datetimestop == '')
			{
				return response()->json(['message' => trans('Required field `stop` is empty')], 415);
			}
			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-86400 seconds')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+86400 seconds')->toDateTimeString();
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
				->where(function ($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get()
				->first();

			if ($exist)
			{
				return response()->json(['message' => trans('Record with provided `crn` already exists')], 415);
			}

			// Fetch information about class from input.
			event($event = new AccountLookup($row));

			$row = $event->account;

			if (!$row->crn)
			{
				// Invalid CRN/classID provided
				return response()->json(['message' => trans('Invalid CRN/classID provided')], 500);
			}

			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-259200 seconds')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+604800 seconds')->toDateTimeString();
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
				$member->userud = $user;
				$member->classaccountid = $row->id;
				$member->datetimestart = $row->start;
				$member->datetimestop = $row->stop;

				if (!$member->save())
				{
					return response()->json(['message' => trans('Failed to create classuser record')], 500);
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
		$request->validate([
			'crn' => 'nullable|string|max:8',
			'department' => 'nullable|string|max:4',
			'coursenumber' => 'nullable|string|max:8',
			'classname' => 'nullable|string|max:255',
			'resourceid' => 'nullable|integer|min:1',
			'groupid' => 'nullable|integer|min:1',
			'userid' => 'nullable|integer|min:1',
			'datetimestart' => 'nullable|date',
			'datetimestop' => 'nullable|date',
		]);

		$row = Account::findOrFail($id);
		$row->fill($request->all());

		if ($row->datetimestart >= $row->datetimestop)
		{
			return response()->json(['message' => trans('Invalid start and stop times')], 415);
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

		if (!$row->trashed())
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
		// Fetch a list of all classaccount IDs from the database.
		$courses = array();
		$errors = array();

		$classdata = Account::query()
			->withTrashed()
			->whereIsActive()
			->where('datetimestop', '>', Carbon::now()->toDateTimeString())
			->where('userid', '>', 0)
			->get();

		foreach ($classdata as $row)
		{
			// Fetch registerants
			event($event = new AccountInstructorLookup($row, $row->user));

			$row = $event->account;

			if ($row->cn)
			{
				$courses[] = $row;
			}
		}

		// Fetch course enrollments
		$students = array();
		foreach ($courses as $course)
		{
			event($event = new AccountEnrollment($course));

			$course = $event->account;
			$count  = 0;

			foreach ($event->enrollments as $student)
			{
				$user = User::query()
					->where('puid', '=', $student->externalId)
					->limit(1)
					->first();

				if (!$user)
				{
					// Nope, sorry. Look them up and post.
					event($event = new UserLookup(['puid' => $student->externalId]));

					$user = $event->user;

					if (!$user)
					{
						$msg = __METHOD__ . '(): Failed to retrieve user ID for organization_id ' . $student->externalId;
						error_log($msg);
						$errors[] = $msg;
						continue;
					}
				}

				// Create a local entry, if one doesn't already exist
				$member = Member::query()
					->withTrashed()
					->whereIsActive()
					->where('classaccountid', '=', $course->id)
					->where('userid', '=', $userid)
					->first();

				if (!$member)
				{
					//$classuser = new classuser();

					$member = new Member();
					$member->userid         = $user->id;
					$member->datetimestart  = $course->datetimestart;
					$member->datetimestop   = $course->datetimestop;
					$member->classaccountid = $course->id;
					$member->notice         = 0;
					$member->membertype     = 0; // 0 = autocreated, 1 = explicit

					if (!$member->save())
					{
						$errors[] = __METHOD__ . '(): Failed to create `classusers` entry for user #' . $user->id . ', class #' . $course->id;
						continue;
					}
				}

				$count++;

				$students[] = $user;
			}

			// Slap student count back into database
			$course->update([
				'studentcount' => $count
			]);
		}

		$users = array();
		$now = Carbon::now();

		// Ok, we got students. Search for students that need access now.
		foreach ($students as $student)
		{
			if ($student->datetimestart <= $now->toDateTimeString()
			 && $student->datetimestop > $now->toDateTimeString())
			{
				$users[] = $student->username;
			}
		}

		// OK! Now we need to get explict users. TAs, instructors, and workshop participants.
		foreach ($classdata as $row)
		{
			// Add instructor starting now
			$users[] = $row->user ? $row->user->username : $row->userid;

			foreach ($row->members()->withTrashed()->whereIsActive()->get() as $extra)
			{
				$users[] = $extra->user ? $extra->user->username : $extra->userid;
			}
		}

		$users = array_unique($users);

		// Get list of current scholar users
		event($event = new CourseEnrollment($users));

		$create_users = $event->create_users;
		$remove_users = $event->remove_users;

		$fortress = Asset::findByName('HPSSUSER');

		$created = array();
		foreach ($create_users as $user)
		{
			$u = new User;
			$u->username = $user;
			$u->primarygroup = 'student';
			$u->loginShell = '/bin/bash';
			$u->quota = 1;
			$u->pilogin = $user;

			// Get current status
			event($event = new ResourceMemberStatus($row->resource, $u));

			if ($event->status >= 400)
			{
				$msg = __METHOD__ . '(): Error getting AIMO ACMaint role info for ' . $user . ': ' . $event->status;
				error_log($msg);
				$errors[] = $msg;
				continue;
			}

			// Is status is pending or ready
			if ($event->status != 2
			 && $event->status != 3)
			{
				// Create account
				event($event = new ResourceMemberCreated($row->resource, $u));

				if ($event->status >= 400)
				{
					$msg = __METHOD__ . '(): Could not create AIMO ACMaint account for ' . $user . ': ' . $event->status;
					error_log($msg);
					$errors[] = $msg;
					continue;
				}
			}

			// Create Fortress
			if ($fortress)
			{
				event($event = new ResourceMemberStatus($fortress, $u));

				if ($event->status >= 400)
				{
					$msg = __METHOD__ . '(): Error getting AIMO ACMaint role info for ' . $user . ': ' . $event->status;
					error_log($msg);
					$errors[] = $msg;
					continue;
				}

				// Is status is pending or ready
				if ($event->status != 2
				&& $event->status != 3)
				{
					// Create account
					event($event = new ResourceMemberCreated($fortress, $u));

					if ($event->status >= 400)
					{
						$msg = __METHOD__ . '(): Could not create AIMO ACMaint account for ' . $user . ': ' . $event->status;
						error_log($msg);
						$errors[] = $msg;
						continue;
					}
				}
				else
				{
					$msg = __METHOD__ . '(): EXISTS ' . $user . ': ' . $event->status;
					error_log($msg);
					$errors[] = $msg;
				}
			}

			if ($event->status)
			{
				$created[] = $user;
			}
		}

		// Do some sanity checking
		// If our net loss here is greater than the new total, something is wrong
		if ((count($remove_users) - count($create_users)) > count($users))
		{
			// TODO: how can we detect and allow normal wipeage during semester turnover?
			$msg = __METHOD__ . '(): Deleting more users than we will have left. This seems wrong!';
			error_log($msg);
			$errors[] = $msg;
		}
		else
		{
			$removed = array();
			foreach ($remove_users as $user)
			{
				// Remove scholar
				event($event = new ResourceMemberDeleted($row->resource, $user));

				if ($event->status >= 400)
				{
					$msg = __METHOD__ . '(): Could not delete AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;
					error_log($msg);
					$errors[] = $msg;
					continue;
				}

				if ($event->status)
				{
					$removed[] = $user;
				}
			}
		}

		$data = array(
			'creating' => $create_users,
			'created'  => $created,
			'removing' => $remove_users,
			'removed'  => $removed,
			'total'    => count($users),
			'errors'   => $errors
		);

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
