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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('finder.facets.filter_' . $key)
			 && $request->input($key) != session()->get('finder.facets.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('finder.facets.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
			'fields.name' => 'required|string|max:150',
			'fields.control_type' => 'required|string|max:150',
			'fields.description' => 'nullable|string',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Facet::findOrFail($id) : new Facet();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$old = $row->facets;
		$current = array();

		if ($request->has('choices'))
		{
			$choices = $request->input('choices', []);

			// Add new or update choices
			foreach ($choices as $choice)
			{
				$c = Facet::find($choice['id']);

				if (!$c || !$c->id)
				{
					$c = new Facet;
				}

				$c->parent = $row->id;
				$c->name = $choice['name'];
				$c->status = 1;
				$c->save();

				$current[] = $c->id;

				$oldmatches = $c->services;
				$currentmatches = array();

				if (!empty($choice['matches']))
				{
					// Add new matches
					foreach ($choice['matches'] as $service_id)
					{
						$match = ServiceFacet::findByServiceAndFacet($service_id, $c->id);

						if (!$match || !$match->id)
						{
							$match = new ServiceFacet;
							$match->service_id = $service_id;
							$match->facet_id = $c->id;
							$match->save();
						}

						$currentmatches[] = $service_id;
					}
				}

				// Remove any previous matches not in the new dataset
				foreach ($oldmatches as $om)
				{
					if (!in_array($om->service_id, $currentmatches))
					{
						$om->delete();
					}
				}
			}

			// Remove any previous choices not in the new dataset
			foreach ($old as $o)
			{
				if (!in_array($o->id, $current))
				{
					$o->delete();
				}
			}
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
