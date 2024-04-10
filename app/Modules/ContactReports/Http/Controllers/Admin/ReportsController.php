<?php

namespace App\Modules\ContactReports\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Comment;
use App\Modules\ContactReports\Models\Reportresource;
use App\Modules\ContactReports\Models\User as ReportUser;
use App\Modules\ContactReports\Models\Type;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

class ReportsController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of entries
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'crm.reports', [
			'timeframe' => 1,
			'search'    => null,
			'tag'       => '',
			'group'     => null,
			'start'     => null,
			'stop'      => null,
			'people'    => null,
			'tag'       => null,
			'resource'  => null,
			'type'      => '*',
			'notice'    => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Report::$orderBy,
			'order_dir' => Report::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'report', 'datetimecreated', 'datetimecontact', 'groupid', 'userid', 'contactreporttypeid']))
		{
			$filters['order'] = Report::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Report::$orderDir;
		}

		$cr = (new Report)->getTable();

		$query = Report::query()
			->select($cr . '.*')
			->with('creator')
			->with('users');

		if ($filters['tag'])
		{
			$query->withTag($filters['tag']);
		}

		if ($filters['people'])
		{
			$query->wherePeople($filters['people']);
		}

		if ($filters['resource'])
		{
			if (is_string($filters['resource']))
			{
				$filters['resource'] = explode(',', $filters['resource']);
			}

			$crr = (new Reportresource)->getTable();

			$query->join($crr, $crr . '.contactreportid', $cr . '.id')
				->whereIn($crr . '.resourceid', $filters['resource']);
		}

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		if ($filters['notice'] != '*')
		{
			$query->where($cr . '.notice', '=', $filters['notice']);
		}

		if ($filters['group'])
		{
			$query->where($cr . '.groupid', '=', $filters['group']);
		}

		if ($filters['type'] != '*')
		{
			$query->where($cr . '.contactreporttypeid', '=', $filters['type']);
		}

		if ($filters['start'])
		{
			$start = Carbon::parse($filters['start']);
			$query->where($cr . '.datetimecontact', '>=', $start->toDateTimeString());
		}

		if ($filters['stop'])
		{
			$stop = Carbon::parse($filters['stop']);
			$query->where($cr . '.datetimecontact', '<', $stop->toDateTimeString());
		}

		$rows = $query
			->with('comments')
			->with('type')
			->with('tags')
			->with('resources')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::all();

		return view('contactreports::admin.reports.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request  $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Report();
		$row->datetimecontact = Carbon::now();

		if ($groupid = $request->input('groupid'))
		{
			$row->groupid = $groupid;
		}

		if ($contactreporttypeid = $request->has('contactreporttypeid'))
		{
			$row->contactreporttypeid = $contactreporttypeid;
		}

		if ($resources = $request->input('resources'))
		{
			$resources = explode(',', $resources);
			foreach ($resources as $r)
			{
				$resource = new Reportresource;
				$resource->resourceid = $r;

				$row->resources->push($resource);
			}
		}

		if ($resources = $request->old('resources'))
		{
			foreach ($resources as $r)
			{
				$resource = new Reportresource;
				$resource->resourceid = $r;

				$row->resources->push($resource);
			}
		}

		if ($people = $request->input('people'))
		{
			$people = explode(',', $people);
			foreach ($people as $p)
			{
				$user = new ReportUser;
				$user->userid = $p;

				$row->users->push($user);
			}
		}

		$groups = array();
		if (\Nwidart\Modules\Facades\Module::isEnabled('groups'))
		{
			$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();
		}
		$types = Type::all();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		if ($people = $request->old('people'))
		{
			$people = explode(',', $people);
			foreach ($people as $p)
			{
				$user = new ReportUser;
				$user->userid = $p;

				$row->users->push($user);
			}
		}

		return view('contactreports::admin.reports.edit', [
			'row'    => $row,
			'groups' => $groups,
			'types'  => $types,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, $id)
	{
		$row = Report::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		if ($resources = $request->old('resources'))
		{
			$row->resources = collect([]);
			foreach ($resources as $r)
			{
				$resource = new Reportresource;
				$resource->resourceid = $r;

				$row->resources->push($resource);
			}
		}

		if ($people = $request->old('people'))
		{
			$people = explode(',', $people);
			$row->users = collect([]);
			foreach ($people as $p)
			{
				$user = new ReportUser;
				$user->userid = $p;

				$row->users->push($user);
			}
		}

		$groups = array();
		if (\Nwidart\Modules\Facades\Module::isEnabled('groups'))
		{
			$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();
		}
		$types = Type::all();

		return view('contactreports::admin.reports.edit', [
			'row'    => $row,
			'groups' => $groups,
			'types'  => $types,
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$now = new Carbon();

		//$request->validate([
		$rules = [
			'fields.report' => 'required',
			'fields.datetimecontact' => 'required|date|before_or_equal:' . $now->format('Y-m-d'),
			'fields.userid' => 'nullable|integer',
			'fields.groupid' => 'nullable|integer',
			'fields.resources' => 'nullable|array',
			'fields.contactreporttypeid' => 'nullable|integer',
			'fields.datetimegroupid' => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Report::findOrNew($id);
		$row->fill($request->input('fields'));
		$row->userid = $row->userid ?: auth()->user()->id;
		$row->notice = Report::NOTICE_NEW;

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		if ($resources = $request->input('resources'))
		{
			$resources = (array)$resources;

			// Fetch current list of resources
			$prior = $row->resources;

			// Remove and add resource-contactreport mappings
			// First calculate diff
			$addresources = array();
			$deleteresources = array();

			// Resources that need removing
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

			// Resources that need adding
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
					$request->session()->flash('warning', 'Failed to delete `contactreportresources` entry #' . $r);
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
					$request->session()->flash('warning', 'Failed to create `contactreportresources` entry #' . $r);
				}
			}
		}

		if ($people = $request->input('people'))
		{
			if (strstr($people, ','))
			{
				$people = explode(',', $people);
			}
			$people = (array)$people;
			foreach ($people as $i => $person)
			{
				if (strstr($person, ':'))
				{
					$person = strstr($person, ':');
					$person = trim($person, ':');
				}
				$people[$i] = $person;
			}

			// Fetch current list of users
			$prior = $row->users;

			// Remove and add mappings
			// First calculate diff
			$addusers = array();
			$deleteusers = array();

			// Users that need removing
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

			// Users that need adding
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
					$request->session()->flash('warning', 'Failed to delete `contactreportusers` entry #' . $r);
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

				$rr = new ReportUser;
				$rr->contactreportid = $row->id;
				$rr->userid = $usr->id;

				if (!$rr->save())
				{
					$request->session()->flash('warning', 'Failed to create `contactreportusers` entry #' . $r);
				}
			}
		}

		return $this->cancel()->withSuccess('Item created!');
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			$row = Report::find($id);

			if (!$row)
			{
				continue;
			}

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
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
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.contactreports.index'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request  $request
	 * @return View
	 */
	public function stats(Request $request)
	{
		$start = Carbon::now()->modify('-30 days');
		$today = Carbon::now()->modify('+1 day');

		// Get filters
		$filters = array(
			'start' => $start->format('Y-m-d'),
			'end'   => $today->format('Y-m-d'),
			'timeframe' => 1,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		$stats = Report::stats($filters['start'], $filters['end']);

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('contactreports::admin.reports.stats', [
			'types' => $types,
			'filters' => $filters,
			'stats' => $stats,
		]);
	}
}
