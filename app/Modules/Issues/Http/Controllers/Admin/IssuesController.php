<?php

namespace App\Modules\Issues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Issueresource;
use App\Modules\Issues\Models\Comment;
use App\Modules\Issues\Models\ToDo;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

class IssuesController extends Controller
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
		$filters = $this->getStatefulFilters($request, 'issues.reports', [
			'search'    => null,
			'tag'       => '',
			'group'     => null,
			'start'     => null,
			'stop'      => null,
			'notice'    => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Issue::$orderBy,
			'order_dir' => Issue::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'userid', 'report', 'datetimecreated', 'issuetodoid']))
		{
			$filters['order'] = Issue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Issue::$orderDir;
		}

		$query = Issue::query()
			->with('creator')
			->with('resources')
			->with('tags')
			->with('comments')
			->where('issuetodoid', '=', 0);

		if ($filters['tag'])
		{
			$query->withTag($filters['tag']);
		}

		if ($filters['search'])
		{
			$keywords = explode(' ', $filters['search']);

			$from_sql = array();
			foreach ($keywords as $keyword)
			{
				// Trim extra garbage
				$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $keyword);

				// Calculate stem for the word
				$stem = PorterStemmer::stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;

				$from_sql[] = "+" . $stem;
			}

			$query->select('*', DB::raw("(MATCH(stemmedreport) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), datetimecreated)) + 1))) AS score"));
			$query->whereRaw("MATCH(stemmedreport) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
			$query->orderBy('score', 'desc');
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
			//->withCount('comments')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$todos = ToDo::query()
			->with('timeperiod')
			->get();

		return view('issues::admin.issues.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'todos'   => $todos
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Issue();
		$row->datetimecreated = Carbon::now();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::admin.issues.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, $id)
	{
		$row = Issue::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::admin.issues.edit', [
			'row' => $row
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
		$rules = [
			'fields.report' => 'required',
			'fields.datetimecreated' => 'nullable|date',
			'fields.userid' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Issue::findOrNew($id);
		$row->fill($request->input('fields'));
		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

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
				$rr = new Issueresource;
				$rr->issueid = $row->id;
				$rr->resourceid = $r;

				if (!$rr->save())
				{
					$request->session()->flash('warning', 'Failed to create `contactreportresources` entry #' . $r);
				}
			}
		}

		return $this->cancel()->withSuccess(trans('global.messages.item created'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $requesy
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
			$row = Issue::findOrFail($id);

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
		return redirect(route('admin.issues.index'));
	}
}
