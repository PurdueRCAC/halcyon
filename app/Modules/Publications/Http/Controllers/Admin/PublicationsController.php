<?php

namespace App\Modules\Publications\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Publications\Models\Type;
use App\Modules\Publications\Models\Publication;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class PublicationsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => 'published',
			'type'     => null,
			'year'     => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Publication::$orderBy,
			'order_dir' => Publication::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('publications.filter_' . $key)
			 && $request->input($key) != session()->get('publications.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('publications.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'title', 'state', 'published_at']))
		{
			$filters['order'] = Publication::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Publication::$orderDir;
		}

		if (!auth()->user() || !auth()->user()->can('manage publications'))
		{
			$filters['state'] = 'published';
		}

		// Get records
		$query = Publication::query()
			->whereState($filters['state']);

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		if ($filters['type'] && $filters['type'] != '*')
		{
			$query->where('type_id', '=', $filters['type']);
		}

		if ($filters['year'] && $filters['year'] != '*')
		{
			$query->whereYear($filters['year']);
		}

		$rows = $query
			->with('type')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		$now = date("Y");
		$start = date("Y");
		$first = Publication::query()
			->orderBy('published_at', 'asc')
			->first();
		if ($first)
		{
			$start = $first->published_at->format('Y');
		}

		$years = array();
		for ($start; $start < $now; $start++)
		{
			$years[] = $start;
		}
		$years[] = $now;
		rsort($years);

		return view('publications::admin.publications.index', [
			'rows' => $rows,
			'filters' => $filters,
			'types' => $types,
			'years' => $years,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  View
	 */
	public function create()
	{
		$row = new Publication();
		$row->state = 1;
		$row->published_at = Carbon::now();

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('publications::admin.publications.edit', [
			'row' => $row,
			'types' => $types,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   int  $id
	 * @return  View
	 */
	public function edit($id)
	{
		$row = Publication::findOrFail($id);

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('publications::admin.publications.edit', [
			'row' => $row,
			'types' => $types,
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
			'type_id' => 'required|integer|min:1',
			'title' => 'required|string|max:500',
			'author' => 'nullable|string|max:3000',
			'editor' => 'nullable|string|max:3000',
			'url' => 'nullable|string|max:2083',
			'series' => 'nullable|string|max:255',
			'booktitle' => 'nullable|string|max:1000',
			'edition' => 'nullable|string|max:100',
			'chapter' => 'nullable|string|max:40',
			'issuetitle' => 'nullable|string|max:255',
			'journal' => 'nullable|string|max:255',
			'issue' => 'nullable|string|max:40',
			'volume' => 'nullable|string|max:40',
			'number' => 'nullable|string|max:40',
			'pages' => 'nullable|string|max:40',
			'publisher' => 'nullable|string|max:500',
			'address' => 'nullable|string|max:300',
			'institution' => 'nullable|string|max:500',
			'organization' => 'nullable|string|max:500',
			'school' => 'nullable|string|max:200',
			'crossref' => 'nullable|string|max:100',
			'isbn' => 'nullable|string|max:50',
			'doi' => 'nullable|string|max:255',
			'note' => 'nullable|string|max:2000',
			'state' => 'nullable|integer',
			'published_at' => 'nullable|datetime',
			//'year' => 'nullable|integer',
			'filename' => 'nullable|string|max:255',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Publication::findOrFail($id) : new Publication();
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if ($request->has('year'))
		{
			$row->published_at = $request->input('year') . '-' . $request->input('month', '01') . ' -01 00:00:00';
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($request->has('file'))
		{
			// Doing this by file extension is iffy at best but
			// detection by contents productes `txt`
			$name = $request->file('file')->getClientOriginalName();
			$name = $row->sanitize($name);

			$parts = explode('.', $name);
			$extension = end($parts);
			$extension = strtolower($extension);

			if (!in_array($extension, ['pdf', 'doc', 'docx', 'rtf', 'txt', 'md']))
			{
				return redirect()->back()->withError(trans('publications::publications.errors.invalid file type'));
			}

			$file = $request->file('file')->store('public/publications/' . $row->id);

			$row->filename = $name;
			$row->save();
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
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
			$row = Publication::findOrFail($id);

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
		return redirect(route('admin.publications.index'));
	}

	/**
	 * Remove the file for specified entry
	 *
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  RedirectResponse
	 */
	public function deletefile(Request $request, $id)
	{
		$row = Publication::findOrFail($id);

		if (!$row->deleteAttachment())
		{
			return redirect()->back()->withError(trans('publications::publications.errors.file delete failed'));
		}

		return redirect(route('admin.publications.edit', ['id' => $id]));
	} 
}
