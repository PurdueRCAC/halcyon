<?php

namespace App\Modules\Themes\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Themes\Models\Theme;
use App\Halcyon\Http\StatefulRequest;

class ThemesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		//$rows = Theme::paginate(20);

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

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('themes.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Theme::query();

		//$e = 'extensions';
		$l = 'languages';
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

		// Filter by extension enabled
		/*$query
			->leftJoin($e, $e . '.element', $s . '.template')
			//->where($e . '.client_id', '=', $s . '.client_id')
			->where($e . '.enabled', '=', 1)
			->where($e . '.type', '=', 'theme');*/

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
				//$s . '.element',
				$s . '.name',
				$s . '.enabled',
				$s . '.client_id',
				//$l . '.title',
				//$l . '.image',
				//$e . '.id'
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
	 * @param  integer $id
	 * @param  Request $request
	 * @return Response
	 */
	public function edit($id, Request $request)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Theme::findOrFail($id);

		if ($fields = app('request')->old('fields'))
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
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.title' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Theme::findOrFail($id) : new Theme();

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
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

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
		return redirect(route('admin.themes.index'));
	}
}
