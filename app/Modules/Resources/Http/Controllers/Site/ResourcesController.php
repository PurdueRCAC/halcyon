<?php

namespace App\Modules\Resources\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Resources\Events\TypeDisplaying;

class ResourcesController extends Controller
{
	/**
	 * Display a listing of resources
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'type'     => 0,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc',
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		$rows = Asset::query()
			->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::orderBy('name', 'asc')->get();

		return view('resources::site.index', [
			'rows'    => $rows,
			'types'   => $types,
			'filters' => $filters
		]);
	}

	/**
	 * Display a listing of resources for a specific type
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function type(Request $request)
	{
		$type = Type::findByName($request->segment(1));

		if (!$type)
		{
			abort(404);
		}

		$rows = $type->resources()
			->with('facets')
			->where('display', '>', 0)
			->where(function ($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->whereNotNull('description')
			->orderBy('display', 'desc')
			->get();

		$type->name = trans('resources::resources.type resources', ['type' => $type->name]);

		event($event = new TypeDisplaying($type));
		$type = $event->type;

		return view('resources::site.type', [
			'type'    => $type,
			'items'   => $rows,
			'rows'    => $rows,
			'retired' => false,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function retired(Request $request)
	{
		$type = Type::findByName($request->segment(1));

		if (!$type)
		{
			abort(404);
		}

		$items = $type->resources()
			->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->whereNotNull('description')
			->orderBy('display', 'desc')
			->get();

		$rows = $type->resources()
			->where('display', '>', 0)
			->onlyTrashed()
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->whereNotNull('description')
			->orderBy('display', 'desc')
			->get();

		$type->name = trans('resources::resources.type retired resources', ['type' => $type->name]);

		event($event = new TypeDisplaying($type));
		$type = $event->type;

		return view('resources::site.type', [
			'type'  => $type,
			'items' => $items,
			'rows'  => $rows,
			'retired' => true,
		]);
	}

	/**
	 * Show the specified resource.
	 * 
	 * @param  Request $request
	 * @param  string  $name
	 * @param  string  $section
	 * @return View
	 */
	public function show(Request $request, $name, $section = null)
	{
		if ($name == 'retired')
		{
			return $this->retired($request);
		}

		$type = Type::findByName($request->segment(1));

		if (!$type)
		{
			abort(404);
		}

		$resource = Asset::findByName($name);

		if (!$resource || !$resource->listname || !$resource->display)
		{
			abort(404);
		}

		event($event = new AssetDisplaying($resource, $section));
		$sections = collect($event->getSections());

		$rows = $type->resources()
			->where('display', '>', 0)
			->orderBy('display', 'desc')
			->get();

		return view('resources::site.show', [
			'type'     => $type,
			'rows'     => $rows,
			'resource' => $resource,
			'sections' => $sections,
		]);
	}
}
