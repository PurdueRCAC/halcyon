<?php

namespace App\Modules\Finder\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Finder\Models\Facet;
use App\Modules\Finder\Models\Service;

class FacetsController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Facet::$orderBy,
			'order_dir' => Facet::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('finder.facets.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array('id', 'name', 'unixgroup', 'members_count')))
		{
			$filters['order'] = Facet::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Facet::$orderDir;
		}

		$query = Facet::query()
			->where('parent', '=', 0);

		$g = (new Facet)->getTable();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($g . '.id', '=', $filters['search']);
			}
			else
			{
				$filters['search'] = strtolower((string)$filters['search']);

				$query->where(function ($where) use ($filters, $g)
				{
					$where->where($g . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($g . '.description', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		if ($filters['state'] == 'published')
		{
			$query->where('status', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('status', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->withTrashed();
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('finder::admin.facets.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$row = new Facet();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$services = Service::query()
			->where('status', '=', 1)
			->orderBy('title', 'asc')
			->get();

		return view('finder::admin.facets.edit', [
			'row' => $row,
			'services' => $services,
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
		//$request->validate([
		$rules = [
			'fields.name' => 'required|max:255',
			'fields.unixgroup' => 'nullable|max:10',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Group::findOrFail($id) : new Group();
		$row->fill($request->input('fields'));

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		if ($row->unixgroup)
		{
			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $row->unixgroup))
			{
				return redirect()->back()->withError(trans('Field `unixgroup` not in valid format'));
			}

			$exists = Group::findByUnixgroup($row->unixgroup);

			// Check for a duplicate
			if ($exists)
			{
				return redirect()->back()->withError(trans('`unixgroup` ' . $row->unixgroup . ' already exists'));
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  integer  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Facet::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$services = Service::query()
			->where('status', '=', 1)
			->orderBy('title', 'asc')
			->get();

		return view('finder::admin.facets.edit', [
			'row' => $row,
			'services' => $services,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Facet::findOrFail($id);

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
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function state(Request $request, $id = null)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans('finder::finder.select to' . ($state ? 'publish' : 'unpublish')));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Facet::findOrFail(intval($id));

			if ($row->status == $state)
			{
				continue;
			}

			$row->timestamps = false;
			$row->status = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'finder::finder.items published'
				: 'finder::finder.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.finder.index'));
	}
}
