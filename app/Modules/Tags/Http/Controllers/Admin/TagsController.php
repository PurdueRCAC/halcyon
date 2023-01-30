<?php

namespace App\Modules\Tags\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Tags\Models\Tag;
use App\Halcyon\Http\StatefulRequest;

class TagsController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'active',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Tag::$orderBy,
			'order_dir' => Tag::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('tags.filter_' . $key)
			 && $request->input($key) != session()->get('tags.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('tags.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'slug', 'created_at', 'updated_at', 'tagged_count', 'alias_count']))
		{
			$filters['order'] = Tag::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Tag::$orderDir;
		}

		$query = Tag::query()
			->where('parent_id', '=', 0);

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where(function($where) use ($filters)
			{
				$where->where('name', 'like', '%' . $filters['search'] . '%')
					->orWhere('slug', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['state'] == 'active')
		{
			// Laravel does this by default
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		$rows = $query
			//->withCount('tagged')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('tags::admin.tags.index', [
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
		$row = new Tag();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('tags::admin.tags.edit', [
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
		$row = Tag::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('tags::admin.tags.edit', [
			'row' => $row,
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
		$rules = [
			'fields.name' => 'required|string|max:150',
			'fields.slug' => 'nullable|string|max:100'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Tag::findOrFail($id) : new Tag();
		$row->fill($request->input('fields'));

		if (!$row->created_by)
		{
			$row->created_by = auth()->user()->id;
		}

		if ($row->id)
		{
			$row->updated_by = auth()->user()->id;
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		$aliases = $request->input('alias', []);

		foreach ($aliases as $alias)
		{
			$a = Tag::all()
				->where('name', '=', $alias['name'])
				->where('parent_id', '=', $id)
				->first();

			if (!$a)
			{
				$a = new Tag;
				$a->parent_id = $id;
				$a->name = $alias['name'];
				$a->created_by = auth()->user()->id;
				$a->save();
			}
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Tag::findOrFail($id);

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
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.tags.index'));
	}
}
