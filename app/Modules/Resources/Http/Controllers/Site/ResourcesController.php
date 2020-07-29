<?php

namespace App\Modules\Resources\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Resources\Entities\Type;

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
			'rows' => $rows
		]);
	}

	/**
	 * Show the specified resource.
	 * @return Response
	 */
	public function show(Request $request, $name)
	{
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
			'resource' => $resource
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function retired(Request $request)
	{
		$id = 1;

		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				__('resources::assets.edit'),
				url('/resources/edit/:id', $id)
			);

		return view('resources::site.edit');
	}
}
