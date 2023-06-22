<?php

namespace App\Modules\ContactReports\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
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
	/**
	 * Display a listing of articles
	 *
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
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
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('crm.reports.filter_' . $key)
			 && $request->input($key) != session()->get('crm.reports.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('crm.reports.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
			->with('creator')
			->with('users');

		if ($filters['tag'])
		{
			$query->withTag($filters['tag']);
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

			$cru = (new ReportUser)->getTable();

			$query->join($cru, $cru . '.contactreportid', $cr . '.id');
			$query->where(function ($where) use ($filters, $cru, $cr)
				{
					$where->whereIn($cru . '.userid', $filters['people'])
						->orWhereIn($cr . '.userid', $filters['people']);
				})
				->groupBy($cr . '.id')
				->groupBy($cr . '.groupid')
				->groupBy($cr . '.userid')
				->groupBy($cr . '.report')
				->groupBy($cr . '.stemmedreport')
				->groupBy($cr . '.datetimecontact')
				->groupBy($cr . '.datetimecreated')
				->groupBy($cr . '.notice')
				->groupBy($cr . '.datetimegroupid')
				->groupBy($cr . '.contactreporttypeid');
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

				$sql  = "(MATCH(stemmedreport) AGAINST ('+";
				$sql .= $keywords[0];
				for ($i=1; $i<count($keywords); $i++)
				{
					$sql .= " +" . $keywords[$i];
				}
				$sql .= "') * 10 + 2 * (1 / (DATEDIFF(NOW(), datetimecontact) + 1))) AS score";

				$query->select(['*', DB::raw($sql)]);

				$sql  = "MATCH(stemmedreport) AGAINST ('+";
				$sql .= $keywords[0];
				for ($i=1; $i<count($keywords); $i++)
				{
					$sql .= " +" . $keywords[$i];
				}
				$sql .= "' IN BOOLEAN MODE)";

				$query->whereRaw($sql)
					->orderBy('score', 'desc');

				//$query->where('report', 'like', '%' . $filters['search'] . '%');
			}
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

		$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();
		$types = Type::all();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
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

		$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();
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

		$row = $id ? Report::findOrFail($id) : new Report();
		$row->fill($request->input('fields'));
		$row->userid = $row->userid ?: auth()->user()->id;
		$row->notice = 23;

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
			$row = Report::findOrFail($id);

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
