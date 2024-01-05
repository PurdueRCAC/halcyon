<?php

namespace App\Modules\Themes\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Themes\Models\Theme;
use App\Halcyon\Http\StatefulRequest;

class ThemesController extends Controller
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
			'search'    => null,
			'element'  => null,
			'client_id' => '*',
			// Pagination
			'limit'     => config('list_limit', 20),
			'page' => 1,
			'order'     => 'name',
			'order_dir' => 'asc',
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('themes.filter_' . $key)
			 && $request->input($key) != session()->get('themes.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('themes.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Theme::query();

		//$l = 'languages';
		//$m = 'menu_items';
		$s = (new Theme)->getTable();

		$query
			/*->select([
				$s . '.id AS id',
				$s . '.element',
				$s . '.name',
				$s . '.enabled',
				$s . '.client_id',
				//'\'0\' AS assigned',
				//$m . '.template_style_id AS assigned',
				//$l . '.title AS language_title',
				//$l . '.image'
			])*/
			->whereIsTheme();

		// Join on menus.
		//$query
		//	->leftJoin($m, $m . '.template_style_id', $s . '.id');

		// Join over the language
		//$query
		//	->leftJoin($l, $l . '.lang_code', $s . '.home');

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($s . '.id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($q) use ($filters)
				{
					$q->where($s . '.name', 'like', $filters['search'])
						->orWhere($s . '.element', 'like', $filters['search']);
				});
			}
		}

		if ($filters['client_id'] != '*')
		{
			$query->where($s . '.client_id', '=', (int)$filters['client_id']);
		}

		if ($filters['element'])
		{
			$query->where($s . '.element', '=', (int)$filters['element']);
		}

		$query
			->groupBy([
				$s . '.id',
				$s . '.element',
				$s . '.folder',
				$s . '.name',
				$s . '.enabled',
				$s . '.access',
				$s . '.protected',
				$s . '.client_id',
				$s . '.type',
				$s . '.checked_out',
				$s . '.checked_out_time',
				$s . '.ordering',
				$s . '.updated_at',
				$s . '.updated_by',
				$s . '.params'
			]);

		// Get records
		$rows = $query
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);;

		//$preview = $this->config->get('template_positions_display');

		return view('themes::admin.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  int $id
	 * @param  Request $request
	 * @return View
	 */
	public function edit($id, Request $request)
	{
		$request->merge(['hidemainmenu' => 1]);

		$row = Theme::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$row->registerLanguage();

		return view('themes::admin.edit', [
			'row'  => $row,
			'form' => $row->getForm()
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
			'fields.name' => 'required|string|max:255',
			'fields.params' => 'nullable|array',
			'fields.client_id' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Theme::findOrNew($id);
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
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
			// Note: This is recursive and will also remove all descendents
			$row = Theme::findOrFail($id);

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
		return redirect(route('admin.themes.index'));
	}
}
