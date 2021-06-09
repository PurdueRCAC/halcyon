<?php

namespace App\Modules\Resources\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Events\AssetDisplaying;

class ResourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return Response
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
			->where('listname', '!=', '')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate(20);

		app('pathway')->append(
			config('resources.name'),
			route('site.resources.index')
		);

		$types = Type::orderBy('name', 'asc')->get();

		return view('resources::site.index', [
			'rows' => $rows,
			'types' => $types,
			'filters' => $filters
		]);
	}

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function type(Request $request)
	{
		$type = Type::findByName($request->segment(1));

		if (!$type)
		{
			abort(404);
		}

		$rows = $type->resources()
			->withTrashed()
			->whereIsActive()
			//->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->whereNotNull('description')
			->orderBy('display', 'desc')
			->get();

		return view('resources::site.type', [
			'type' => $type,
			'items' => $rows,
			'rows' => $rows,
			'retired' => false,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function retired(Request $request)
	{
		$type = Type::findByName($request->segment(1));

		if (!$type)
		{
			abort(404);
		}

		$items = $type->resources()
			->withTrashed()
			->whereIsActive()
			//->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->whereNotNull('description')
			->orderBy('display', 'desc')
			->get();

		$rows = $type->resources()
			->withTrashed()
			->whereIsTrashed()
			//->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->whereNotNull('description')
			->orderBy('display', 'desc')
			->get();

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
	 * @return Response
	 */
	public function show(Request $request, $name)
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

		if (!$resource || !$resource->listname)
		{
			abort(404);
		}

		event($event = new AssetDisplaying($resource));
		$sections = collect($event->getSections());

		app('pathway')
			->append(
				$type->name,
				route('site.resources.type.' . $type->alias)
			)
			->append(
				$resource->name,
				route('site.resources.' . $type->alias . '.show', ['name' => ($resource->listname ? $resource->listname : $resource->rolename)])
			);

		$rows = $type->resources()
			->withTrashed()
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where('display', '>', 0)
			->orderBy('display', 'desc')
			->get();

		return view('resources::site.show', [
			'type' => $type,
			'rows' => $rows,
			'resource' => $resource,
			'sections' => $sections,
		]);
	}
}
