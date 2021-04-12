<?php

namespace App\Modules\Tags\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Tags\Models\Tag;
use App\Modules\Tags\Models\Tagged;
use App\Halcyon\Http\StatefulRequest;

class TaggedController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page' => 1,
			// Sorting
			'order'     => Tagged::$orderBy,
			'order_dir' => Tagged::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('tagged.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'taggable_id', 'taggable_type', 'created_at']))
		{
			$filters['order'] = Tagged::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Tagged::$orderDir;
		}

		$tag = Tag::findOrFail($filters['tag_id']);

		$query = $tag->tagged();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where('taggable_type', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			//->withCount('tagged')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('tags::admin.tagged.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$row = new Tag();

		return view('tags::admin.tagged.edit', [
			'row' => $row
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
		$request->validate([
			'fields.name' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Tag::findOrFail($id) : new Tag();
		$row->fill($request->input('fields'));
		$row->slug = $row->normalize($row->name);

		if (!$row->created_by)
		{
			$row->created_by = auth()->user()->id;
		}

		if (!$row->updated_by)
		{
			$row->updated_by = auth()->user()->id;
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
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Tag::findOrFail($id);

		return view('tags::admin.tagged.edit', [
			'row' => $row,
		]);
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
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.tags.index'));
	}
}
