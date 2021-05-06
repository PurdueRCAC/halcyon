<?php

namespace App\Modules\Issues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Issueresource;
use App\Modules\Issues\Models\Comment;
use App\Modules\Issues\Models\ToDo;
use App\Halcyon\Utility\PorterStemmer;

class IssuesController extends Controller
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
			'group'     => null,
			'start'     => null,
			'stop'      => null,
			'notice'    => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Issue::$orderBy,
			'order_dir' => Issue::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('issues.reports.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Issue)->getAttributes())))
		{
			$filters['order'] = Issue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Issue::$orderDir;
		}

		$query = Issue::query()
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
				$stem = PorterStemmer::Stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;

				$from_sql[] = "+" . $stem;
			}

			$query->select('*', DB::raw("(MATCH(stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), $n.datetimecreated)) + 1))) AS score"));
			$query->whereRaw("MATCH(stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
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
			->withCount('comments')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$todos = ToDo::query()
			->withTrashed()
			->whereIsActive()
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
	 * @return  Response
	 */
	public function create()
	{
		$row = new Issue();

		if ($fields = app('request')->old('fields'))
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
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = Issue::findOrFail($id);

		if ($fields = app('request')->old('fields'))
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
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.report' => 'required',
			'fields.datetimecreated' => 'nullable|datetime',
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

		$row = $id ? Issue::findOrFail($id) : new Issue();
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
			$row = Issue::findOrFail($id);

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
		return redirect(route('admin.issues.index'));
	}
}
