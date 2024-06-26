<?php

namespace App\Modules\Tags\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Modules\Tags\Models\Tag;
use App\Halcyon\Http\Concerns\UsesFilters;

class TagsController extends Controller
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
		$filters = $this->getStatefulFilters($request, 'tags', [
			'search'    => null,
			'state'     => 'active',
			'domain'    => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Tag::$orderBy,
			'order_dir' => Tag::$orderDir,
		]);

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

		if ($filters['domain'])
		{
			$query->where('domain', '=', $filters['domain']);
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
			//->withCount('aliases')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$domains = Tag::query()
			->select(DB::raw('DISTINCT(domain)'))
			->orderBy('domain', 'asc')
			->get();

		return view('tags::admin.tags.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'domains' => $domains,
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
		$row = new Tag();

		if ($fields = $request->old('fields'))
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
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Tag::findOrFail($id);

		if ($fields = $request->old('fields'))
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
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.name' => 'required|string|max:150',
			'fields.slug' => 'nullable|string|max:100',
			'fields.domain' => 'nullable|string|max:100',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Tag::findOrNew($id);
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
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Tag::find($id);

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
		return redirect(route('admin.tags.index'));
	}
}
