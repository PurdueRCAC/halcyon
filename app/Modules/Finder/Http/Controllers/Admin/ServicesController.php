<?php

namespace App\Modules\Finder\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Finder\Models\Service;
use App\Modules\Finder\Models\Field;
use App\Modules\Finder\Models\ServiceField;

class ServicesController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of tags
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'finder.services', [
			'search'    => null,
			'state'     => 'published',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Service::$orderBy,
			'order_dir' => Service::$orderDir,
		]);

		if (!in_array($filters['order'], array('id', 'name')))
		{
			$filters['order'] = Service::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Service::$orderDir;
		}

		$query = Service::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$search = strtolower((string)$filters['search']);

					$where->where('title', 'like', '% ' . $search . '%')
						->orWhere('title', 'like', $search . '%');
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

		return view('finder::admin.services.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$row = new Service();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$fields = Field::query()
			->where('status', '=', 1)
			->orderBy('weight', 'asc')
			->get();

		return view('finder::admin.services.edit', [
			'row' => $row,
			'fields' => $fields
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Service::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$fields = Field::query()
			->where('status', '=', 1)
			->orderBy('weight', 'asc')
			->get();

		return view('finder::admin.services.edit', [
			'row' => $row,
			'fields' => $fields
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'fields.title' => 'required|string|max:255',
			'fields.summary' => 'nullable|string|max:1200'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Service::findOrNew($id);
		if (!$row->id)
		{
			$row->status = 1;
		}
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($request->has('sfields'))
		{
			$sfields = $request->input('sfields');

			foreach ($sfields as $name => $value)
			{
				$field = Field::findByName($name);

				if (!$field)
				{
					continue;
				}

				$fs = ServiceField::findByServiceAndField($row->id, $field->id);

				if (!$fs || ! $fs->id)
				{
					$fs = new ServiceField;
				}

				$fs->service_id = $row->id;
				$fs->field_id = $field->id;
				$fs->value = $value;
				$fs->save();
			}
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Service::findOrFail($id);

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
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @param   int  $id
	 * @return  RedirectResponse
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
			$row = Service::findOrFail(intval($id));

			if ($row->status == $state)
			{
				continue;
			}

			$row->timestamps = false;
			$row->status = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', trans('global.messages.save failed'));
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
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.finder.services'));
	}
}
