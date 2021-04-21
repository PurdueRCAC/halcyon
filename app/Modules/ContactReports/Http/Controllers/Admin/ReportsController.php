<?php

namespace App\Modules\ContactReports\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Comment;
use App\Modules\ContactReports\Models\Reportresource;
use App\Modules\ContactReports\Models\User as ReportUser;
use App\Modules\ContactReports\Models\Type;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

class ReportsController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'tag'       => '',
			'group'    => null,
			'start'    => null,
			'stop'     => null,
			'type'     => '*',
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

		if ($filters['tag'])
		{
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
				$stem = PorterStemmer::Stem($keyword);
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
			$query->where('notice', '=', $filters['notice']);
		}

		if ($filters['group'])
		{
			$query->where('groupid', '=', $filters['group']);
		}

		if ($filters['type'] != '*')
		{
			$query->where('contactreporttypeid', '=', $filters['type']);
		}

		$rows = $query
			->withCount('comments')
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
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$row = new Report();

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
	 * @param   integer  $id
	 * @return  Response
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
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$now = new Carbon();

		//$request->validate([
		$rules = [
			'fields.report' => 'required',
			'fields.datetimecontact' => 'required|date|before_or_equal:' . $now->toDateTimeString(),
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
			$people = (array)$people;

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
	 * Remove the specified entry
	 *
	 * @param   Request $request
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
		return redirect(route('admin.contactreports.index'));
	}
}
