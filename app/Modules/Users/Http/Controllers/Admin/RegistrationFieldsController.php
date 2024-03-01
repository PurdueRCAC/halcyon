<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Access\Viewlevel as Level;
use App\Modules\Users\Models\RegistrationField;
use App\Halcyon\Http\StatefulRequest;

class RegistrationFieldsController extends Controller
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
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Level::$orderBy,
			'order_dir' => Level::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key)
			 && $request->input($key) != session()->get('users.registration.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('users.registration.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = RegistrationField::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = RegistrationField::$orderDir;
		}

		$query = RegistrationField::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('users::admin.registration.index', [
			'rows'    => $rows,
			'filters' => $filters
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
		$row = new RegistrationField;

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.registration.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = RegistrationField::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.registration.edit', [
			'row' => $row
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
		$rules = [
			'fields.name' => 'required|string|max:100',
			'fields.type' => 'required|string|max:100',
			'fields.required' => 'nullable|integer',
			'fields.include_admin' => 'nullable|integer',
		];
		$inputs = $request->all();
		if ($inputs['fields']['type'] === 'select')
		{
			$rules['fields.options'] = 'required|array';
		}
		$inputs['fields']['options'] = array_filter($inputs['fields']['options']);

		$validator = Validator::make($inputs, $rules, [
			'fields.options.required' => 'At least one option must be provided.',
			'fields.name.required' => 'You must provide a name for the text field.'
		]);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = RegistrationField::findOrNew($id);
		$fields = $request->input('fields');
		$row->fill($fields);
		$row->required = $fields['required'] ?? 0;
		$row->include_admin = $fields['include_admin'] ?? 0;

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
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
			$row = RegistrationField::find($id);

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
	 * Return to the main view
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.users.registration'));
	}
}
