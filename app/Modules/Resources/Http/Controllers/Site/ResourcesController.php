<?php

namespace App\Modules\Resources\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Resources\Entities\Type;
use App\Modules\Resources\Events\AssetDisplaying;

class ResourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		$rows = Asset::paginate(20);

		app('pathway')->append(
			config('resources.name'),
			url('/resources')
		);

		return view('resources::site.index', ['rows' => $rows]);
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

		app('pathway')->append(
			$type->name,
			url('/' . strtolower($type->name))
		);

		$rows = $type->resources()
			->where('display', '>', 0)
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
	 * @return Response
	 */
	public function retired(Request $request)
	{
		$type = Type::findByName($request->segment(1));

		if (!$type)
		{
			abort(404);
		}

		app('pathway')->append(
			$type->name,
			url('/' . strtolower($type->name))
		)->append(
			trans('resources::resources.retired'),
			route('site.resources.' . strtolower($type->name) . '.retired')
		);

		$items = $type->resources()
			->where('display', '>', 0)
			->orderBy('display', 'desc')
			->get();

		$rows = $type->resources()
			->onlyTrashed()
			->where('datetimeremoved', '!=', '0000-00-00 00:00:00')
			->where('display', '>', 0)
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

		if (!$resource)
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
				route('site.resources.' . $type->alias . '.show', ['name' => $resource->listname])
			);

		$rows = $type->resources()
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
