<?php

namespace App\Modules\ContactReports\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Comment;
use App\Modules\ContactReports\Models\Reportresource;
use App\Modules\ContactReports\Models\User as ReportUser;
use Carbon\Carbon;

class ReportsController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'group'    => null,
			'start'    => null,
			'stop'     => null,
			'notice'   => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Report::$orderBy,
			'order_dir' => Report::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('crm.reports.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Report)->getAttributes())))
		{
			$filters['order'] = Report::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Report::$orderDir;
		}

		$query = Report::query();

		if ($filters['search'])
		{
			$query->where('report', 'like', '%' . $filters['search'] . '%');
			/*$query->where(function($query) use ($filters)
			{
				$query->where('headline', 'like', '%' . $filters['search'] . '%')
					->orWhere('body', 'like', '%' . $filters['search'] . '%');
			});*/
		}

		if ($filters['notice'] != '*')
		{
			$query->where('notice', '=', $filters['notice']);
		}

		if ($filters['group'])
		{
			$query->where('groupid', '=', $filters['group']);
		}

		$rows = $query
			->withCount('comments')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('contactreports::admin.reports.index', [
			'filters' => $filters,
			'rows'    => $rows,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Report();

		$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();

		return view('contactreports::admin.reports.edit', [
			'row'   => $row,
			'groups' => $groups
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$now = new Carbon();

		$request->validate([
			'fields.report' => 'required',
			'fields.datetimecontact' => 'required|date|before_or_equal:' . $now->toDateTimeString(),
			'fields.userid' => 'nullable|integer',
			'fields.groupid' => 'nullable|integer',
			'fields.datetimegroupid' => 'nullable|date|before_or_equal:' . $now->toDateTimeString(),
		]);

		$id = $request->input('id');

		$row = $id ? Report::findOrFail($id) : new Report();
		$row->fill($request->input('fields'));

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
					$request->session()->flash('warning', 'Failed to delete `contactreportusers` entry #' . $r);
				}
			}

			// Ensure unique-ness
			$addusers = array_unique($addusers);

			foreach ($addusers as $r)
			{
				$rr = new ReportUser;
				$rr->contactreportid = $row->id;
				$rr->userid = $r;

				if (!$rr->save())
				{
					$request->session()->flash('warning', 'Failed to create `contactreportusers` entry #' . $r);
				}
			}
		}

		return $this->cancel()->withSuccess('Item created!');
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Report::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();

		return view('contactreports::admin.reports.edit', [
			'row'   => $row,
			'groups' => $groups
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
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
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
		return redirect(route('admin.contactreports.index'));
	}
}
