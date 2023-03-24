<?php

namespace App\Modules\Resources\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Models\FacetType;
use App\Modules\Resources\Models\FacetOption;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
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
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('resources.types.filter_' . $key)
			 && $request->input($key) != session()->get('resources.types.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('resources.types.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'description']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		$query = Type::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}
		}

		// Build query
		$rows = $query
			->withCount('resources')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('resources::admin.types.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
		$row = new Type();

		return view('resources::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Type::find($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('resources::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.name' => 'required|string|max:20',
			'fields.description' => 'nullable|string'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = (int)$request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();
		$row->name = $request->input('fields.name');
		$row->description = $request->input('fields.description');

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($request->has('facets'))
		{
			$facets = json_decode($request->input('facets'));

			// Collect old fields
			$oldFields = array();
			foreach ($row->facetTypes as $oldField)
			{
				$oldFields[$oldField->id] = $oldField;
			}

			foreach ($facets->fields as $i => $element)
			{
				$field = null;

				$fid = (isset($element->field_id) ? $element->field_id : 0);

				if ($fid && isset($oldFields[$fid]))
				{
					$field = $oldFields[$fid];

					// Remove found fields from the list
					// Anything remaining will be deleted
					unset($oldFields[$fid]);
				}

				$field = $field ?: new FacetType; //FacetType::find($fid));
				//$field = $field ?: new FacetType;
				$field->fill([
					'type_id'       => $row->id,
					'type'          => (string) $element->field_type,
					'label'         => (string) $element->label,
					'name'          => (string) $element->name,
					'description'   => (isset($element->field_options->description) ? (string) $element->field_options->description : null),
					//'required'     => (isset($element->required) ? (int) $element->required : 0),
					'ordering'      => ($i + 1),
					'min'           => (isset($element->field_options->min) ? (int) $element->field_options->min : 0),
					'max'           => (isset($element->field_options->max) ? (int) $element->field_options->max : 0),
					'default_value' => (isset($element->field_options->value) ? (string) $element->field_options->value : null),
					'placeholder'   => (isset($element->field_options->placeholder) ? (string) $element->field_options->placeholder : null)
				]);

				if ($field->type == 'paragraph')
				{
					$field->type = 'textarea';
				}

				if (!$field->save())
				{
					continue;
				}

				// Collect old options
				$oldOptions = array();
				foreach ($field->options as $oldOption)
				{
					$oldOptions[$oldOption->id] = $oldOption;
				}

				// Does this field have any set options?
				if (isset($element->field_options->options))
				{
					foreach ($element->field_options->options as $k => $opt)
					{
						$option = null;

						$oid = (isset($opt->field_id) ? $opt->field_id : 0);

						if ($oid && isset($oldOptions[$oid]))
						{
							$option = $oldOptions[$oid];

							// Remove found options from the list
							// Anything remaining will be deleted
							unset($oldOptions[$oid]);
						}

						$option = $option ?: new FacetOption;
						$option->fill([
							'facet_type_id' => $field->id,
							'label'      => (string) $opt->label,
							'value'      => (isset($opt->value)   ? (string) $opt->value : null),
							'checked'    => (isset($opt->checked) ? (int) $opt->checked : 0),
							'ordering'   => ($k + 1),
						]);

						if (!$option->save())
						{
							continue;
						}
					}
				}

				// Remove any options not in the incoming list
				foreach ($oldOptions as $option)
				{
					$option->delete();
				}
			}

			// Remove any fields not in the incoming list
			foreach ($oldFields as $field)
			{
				$field->delete();
			}
		}

		return $this->cancel()->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
			$row = Type::findOrFail($id);

			if ($row->resources()->count())
			{
				$request->session()->flash('error', trans('resources::resources.errors.type has resources', ['count' => $row->resources()->count()]));
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
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.resources.types'));
	}
}
