<?php

namespace App\Modules\Courses\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Courses\Events\AccountLookup;
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
	 * 		"name":          "userid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
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
			'deptnumber' => $request->input('deptnumber', 0),
			// Paging
			'limit'      => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'      => $request->input('order', Account::$orderBy),
			'order_dir'  => $request->input('order_dir', Account::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Account::$orderDir;
		}

		$g = (new Account)->getTable();

		$query = Account::query()
			->select($g . '.*');

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where($g . '.classname', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['userid'])
		{
			$query->where($g . '.userid', '=', $filters['userid']);
		}

		if ($filters['groupid'])
		{
			$query->where($g . '.groupid', '=', $filters['groupid']);
		}

		if ($filters['resourceid'])
		{
			$query->where($g . '.resourceid', '=', $filters['resourceid']);
		}

		if ($filters['deptnumber'])
		{
			$query->where($g . '.deptnumber', '=', $filters['deptnumber']);
		}

		$rows = $query
			->orderBy($g . '.' . $filters['order'], $filters['order_dir'])
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
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'crn' => 'nullable|string|max:8',
			'department' => 'nullable|string|max:4',
			'coursenumber' => 'nullable|string|max:8',
			'classname' => 'required|string|max:255',
			'resourceid' => 'required|integer|min:1',
			'groupid' => 'nullable|integer|min:1',
			'userid' => 'nullable|integer|min:1',
			'datetimestart' => 'required|datetime',
		]);

		$type = $request->input('type');

		$row = new Account;
		$row->crn = $request->input('crn');
		$row->department = $request->input('department');
		$row->coursenumber = $request->input('coursenumber');
		$row->classname = $request->input('classname');
		$row->resourceid = $request->input('resourceid');
		$row->groupid = $request->input('groupid');
		$row->userid = $request->input('userid');
		$row->datetimestart = $request->input('datetimestart');
		//$row->fill($request->all());

		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

		// Swith for class vs workshop
		if ($type == 'workshop')
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

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
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
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "department",
	 * 		"description":   "Class department",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "classname",
	 * 		"description":   "Name of the class",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) the class starts",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) the class stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time"
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
			'datetimestart' => 'nullable|datetime',
			'datetimestop' => 'nullable|datetime',
		]);

		$row = Account::findOrFail($id);
		$row->fill($request->all());

		if ($row->datetimestart >= $row->datetimestop)
		{
			return response()->json(['message' => trans('Invalid start and stop times')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
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

		$classdata = Account::query()
			->where('datetimestop', '>', Carbon::now()->toDateTimeString())
			->where('userid', '>', 0)
			->get();

		foreach ($classdata as $row)
		{
			// Fetch registerants
			event($event = new AccountLookupInstructor($row, $row->user));

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

			foreach ($enrollments as $student)
			{
				$user = User::query()
					->where('organization_id', '=', $student->externalId)
					->limit(1)
					->first();

				if (!$user)
				{
					// Nope, sorry. Look them up and post.
					event($event = new UserLookup(['puid' => $student->externalId]));

					$user = $event->user;

					if (!$user)
					{
						error_log(__METHOD__ . '(): Failed to retrieve user ID for organization_id ' . $student->externalId);
						continue;
					}
				}

				$count++;

				/*$member = new Member;
				$member->datetimestart = $course->datetimestart;
				$member->datetimestop = $course->datetimestop;*/

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
			$users[] = $row->user->username;

			foreach ($row->members as $extra)
			{
				$users[] = $extra->user->username;
			}
		}

		// Get list of current scholar users
		event($event = new CourseEnrollment($users));

		$create_users = $event->create_users;
		$remove_users = $event->remove_users;

		// Do some sanity checking
		// If our net loss here is greater than the new total, something is wrong
		if ((count($remove_users) - count($create_users)) > count($users))
		{
			// TODO: how can we detect and allow normal wipeage during semester turnover?
			error_log(__METHOD__ . '(): Deleting more users than we will have left. This seems wrong!');
		}

		$created = array();
		foreach ($create_users as $user)
		{
			event($event = new MemberAdd($user));

			if ($event->status)
			{
				$created[] = $user;
			}
		}

		$removed = array();
		foreach ($remove_users as $user)
		{
			event($event = new MemberRemove($user));

			if ($event->status)
			{
				$removed[] = $user;
			}
		}

		$data = array(
			'creating' => $create_users,
			'created'  => $created,
			'removing' => $remove_users,
			'removed'  => $removed,
			'total'    => $users
		);

		return response()->json($data);
	}
}
