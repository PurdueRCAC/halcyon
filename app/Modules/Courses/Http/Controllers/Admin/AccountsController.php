<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Events\InstructorLookup;
use App\Modules\Courses\Mail\Composed;
use App\Modules\Resources\Models\Asset;
use App\Modules\Users\Models\User;
use App\Modules\History\Models\Log;
use Carbon\Carbon;

class AccountsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search' => '',
			//'resourceid'  => 0,
			'userid' => null,
			'semester' => '',
			'state' => 'active',
			'start'     => null,
			'stop'       => null,
			// Paging
			'limit' => config('list_limit', 20),
			'page' => 1,
			// Sorting
			'order' => 'datetimestart',
			'order_dir' => 'asc'
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('courses.filter_' . $key)
			 && $request->input($key) != session()->get('courses.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('courses.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		$query = Account::query()
			->with('user')
			->with('resource');

		if ($filters['search'])
		{
			$query->where('classname', 'like', '%' . $filters['search'] . '%');
		}

		/*if ($filters['resourceid'])
		{
			$query->where('resourceid', '=', $filters['resourceid']);
		}*/

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['semester'])
		{
			$query->where('semester', '=', $filters['semester']);
		}

		if ($filters['start'])
		{
			$query->where('datetimestart', '<=', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('datetimestop', '>', $filters['stop']);
		}

		if ($filters['state'] == 'active')
		{
			$query->where(function ($where)
				{
					$where->whereNull('datetimestop')
						->orWhere('datetimestop', '>', Carbon::now()->toDateTimeString());
				});
		}
		elseif ($filters['state'] == 'inactive')
		{
			$query->whereNotNull('datetimestop');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		if ($request->has('export'))
		{
			$rows = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->get();

			return $this->export($rows, $request->input('export'));
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$semesters = Account::query()
			->select(DB::raw('DISTINCT(semester)'))
			->orderBy('semester', 'desc')
			->get();

		return view('courses::admin.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'semesters' => $semesters,
		]);
	}

	/**
	 * Download a list of records
	 * 
	 * @param  object  $rows
	 * @return Response
	 */
	public function export($rows, $export)
	{
		$data = array();
		$data[] = array(
			trans('courses::courses.id'),
			trans('courses::courses.course number'),
			trans('courses::courses.course name'),
			trans('courses::courses.semester'),
			trans('courses::courses.date start'),
			trans('courses::courses.date stop'),
			trans('courses::courses.resource'),
			trans('courses::courses.user'),
			trans('courses::courses.username'),
			trans('courses::courses.email'),
			trans('courses::courses.member type'),
		);

		$courses = array();
		foreach ($rows as $row)
		{
			if (in_array($row->id, $courses))
			{
				continue;
			}

			$courses[] = $row->id;

			$data[] = array(
				$row->id,
				$row->department . ' ' . $row->coursenumber,
				$row->classname,
				$row->semester,
				$row->datetimestart->format('Y-m-d'),
				$row->datetimestop->format('Y-m-d'),
				($row->resource ? $row->resource->name : ''),
				($row->user ? $row->user->name : ''),
				($row->user ? $row->user->username : ''),
				($row->user ? $row->user->email : ''),
				($row->user ? 'Instructor' : '')
			);

			if ($export == 'users')
			{
				foreach ($row->members as $member)
				{
					$data[] = array(
						$row->id,
						$row->department . ' ' . $row->coursenumber,
						$row->classname,
						$row->semester,
						$row->datetimestart->format('Y-m-d'),
						$row->datetimestop->format('Y-m-d'),
						($row->resource ? $row->resource->name : ''),
						($member->user ? $member->user->name : ''),
						($member->user ? $member->user->username : ''),
						($member->user ? $member->user->email : ''),
						($member->membertype == 2 ? 'Instructor/TA' : 'student')
					);
				}
			}
		}

		$filename = 'courses_data.csv';

		$headers = array(
			'Content-type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		);

		$callback = function() use ($data)
		{
			$file = fopen('php://output', 'w');

			foreach ($data as $datum)
			{
				fputcsv($file, $datum);
			}
			fclose($file);
		};

		return response()->streamDownload($callback, $filename, $headers);

		// Set headers and output
		return new Response($output, 200, [
			'Content-Type' => 'text/csv;charset=UTF-8',
			'Content-Disposition' => 'attachment; filename="' . $file . '.csv"',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$row = new Account();
		$row->userid = $request->input('userid');
		$row->datetimestart = Carbon::now();
		$row->datetimestop = Carbon::now()->modify('+5 months');

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		event($event = new InstructorLookup($row->user, false));

		$courses = $event->courses;
		$resources = (new Asset)->tree();

		return view('courses::admin.edit', [
			'row'       => $row,
			'classes'   => $courses,
			'resources' => $resources,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit(Request $request, $id)
	{
		$row = Account::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		event($event = new InstructorLookup($row->user, false));

		$courses = $event->courses;
		$resources = (new Asset)->tree();

		return view('courses::admin.edit', [
			'row'       => $row,
			'classes'   => $courses,
			'resources' => $resources,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.crn' => 'nullable|string|max:8',
			'fields.department' => 'nullable|string|max:4',
			'fields.coursenumber' => 'nullable|string|max:8',
			'fields.classname' => 'required|string|max:255',
			'fields.resourceid' => 'required|integer|min:1',
			'fields.groupid' => 'nullable|integer|min:1',
			'fields.userid' => 'nullable|integer|min:1',
			'fields.datetimestart' => 'required|date',
			'fields.datetimestop' => 'nullable|date',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');
		$type = $request->input('type');

		$row = $id ? Account::findOrFail($id) : new Account();
		$row->fill($request->input('fields'));
		$row->classname = $request->input('fields.classname');
		if ($request->has('fields.coursenumber'))
		{
			$row->coursenumber = $request->input('fields.coursenumber');
		}
		if ($request->has('crn'))
		{
			$row->crn = $request->input('crn');
		}

		if ($type == 'workshop')
		{
			if ($row->classname == '')
			{
				return redirect()->back()->withInput($request->input())->withError(trans('courses::courses.error.empty classname'));
			}
			if ($row->datetimestart == '')
			{
				return redirect()->back()->withInput($request->input())->withError(trans('courses::courses.error.empty start'));
			}
			if ($row->datetimestop == '')
			{
				return redirect()->back()->withInput($request->input())->withError(trans('courses::courses.error.empty stop'));
			}
			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-86400 seconds')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+86400 seconds')->toDateTimeString();
			$row->crn = substr(uniqid(), 0, 8);
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
				->get()
				->first();

			if ($exist)
			{
				return redirect()->back()->withError(trans('courses::courses.error.duplicate crn'));
			}

			// Fetch information about class from input.
			event($event = new AccountLookup($row));

			$row = $event->account;

			if (!$row->crn)
			{
				// Invalid CRN/classID provided
				return redirect()->back()->withError(trans('courses::courses.error.invalid class'));
			}

			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-259200 seconds')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+604800 seconds')->toDateTimeString();
			$row->notice = 1;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Job::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.courses.index'));
	}

	/**
	 * Sync users with account info
	 *
	 * @return  Response
	 */
	public function sync()
	{
		Artisan::call('courses:sync', [
			'-v' => 1
		]);

		$output = Artisan::output();

		$data = explode("\n", $output);

		$response = new \stdClass;
		$response->output = $data;

		return response()->json($response);
	}

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function mail(Request $request)
	{
		$filters = array(
			'search'     => '',
			//'resourceid' => 0,
			'userid'     => null,
			'semester'   => '',
			'state'      => 'active',
			'start'      => null,
			'stop'       => null,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		$query = Account::query();

		if ($filters['search'])
		{
			$query->where('classname', 'like', '%' . $filters['search'] . '%');
		}

		/*if ($filters['resourceid'])
		{
			$query->where('resourceid', '=', $filters['resourceid']);
		}*/

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['semester'])
		{
			$query->where('semester', '=', $filters['semester']);
		}

		if ($filters['start'])
		{
			$query->where('datetimestart', '<=', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('datetimestop', '>', $filters['stop']);
		}

		if ($filters['state'] == 'active')
		{
			$query->where(function ($where)
				{
					$where->whereNull('datetimestop')
						->orWhere('datetimestop', '>', Carbon::now()->toDateTimeString());
				});
		}
		elseif ($filters['state'] == 'inactive')
		{
			$query->whereNotNull('datetimestop');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		$rows = $query->get();

		$users = array();
		foreach ($rows as $row)
		{
			if (!isset($users[$row->userid]))
			{
				$users[$row->userid] = array();
			}
			$users[$row->userid][] = $row;
		}

		return view('courses::admin.mail', [
			'users' => $users,
		]);
	}

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function send(Request $request)
	{
		$rules = [
			'subject' => 'required|string|max:255',
			'body'    => 'required|string',
			'user'    => 'required|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()->withError($validator->messages());
		}

		$subject = $request->input('subject');
		$body    = $request->input('body');
		$users   = $request->input('user', []);
		$bcc     = $request->input('bcc');
		if (strstr($bcc, ','))
		{
			$bcc = explode(',', $bcc);
		}
		$bcc = (array)$bcc;

		if (!empty($bcc))
		{
			foreach ($bcc as $k => $userid)
			{
				$user = User::find($userid);

				if (!$user)
				{
					$request->session()->flash('error', "Could not find account for BCC user ID {$userid}.");
					continue;
				}

				if (!$user->email)
				{
					$request->session()->flash('error', "Email address not found for BCC user {$user->name}.");
					continue;
				}

				$bcc[$k] = $user->email;
			}
		}

		$success = 0;
		foreach ($users as $userid)
		{
			$user = User::find($userid);

			if (!$user)
			{
				$request->session()->flash('error', "Could not find account for user ID {$userid}.");
				continue;
			}

			// Prepare and send actual email
			$message = new Composed($user, $subject, $body);

			if (!$user->email)
			{
				$request->session()->flash('error', "Email address not found for user {$user->name}.");
				continue;
			}

			$mail = Mail::to($user->email);
			if (!empty($bcc))
			{
				$mail->bcc($bcc);
			}
			$mail->send($message);

			Log::create([
				'ip'              => $request->ip(),
				'userid'          => (auth()->user() ? auth()->user()->id : 0),
				'status'          => 200,
				'transportmethod' => 'POST',
				'servername'      => $request->getHttpHost(),
				'uri'             => Str::limit($user->email, 128, ''),
				'app'             => Str::limit('email', 20, ''),
				'payload'         => Str::limit('Emailed composed message to class user.', 2000, ''),
				'classname'       => Str::limit(__CLASS__, 32, ''),
				'classmethod'     => Str::limit('send', 16, ''),
				'targetuserid'    => (int)$user->id,
			]);

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('courses::courses.email sent', ['count' => $success]));
		}

		return $this->cancel();
	}
}
