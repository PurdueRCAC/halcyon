<?php

namespace App\Modules\Storage\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Storage\Models\StorageResource;

class StorageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @return View
	 */
	public function index()
	{
		$rows = StorageResource::paginate(20);

		app('pathway')->append(
			trans('storage::storage.module name'),
			route('site.storage.index')
		);

		return view('storage::site.index', [
			'rows' => $rows
		]);
	}

	/**
	 * Show the specified resource.
	 * 
	 * @param  Request $request
	 * @param  string  $name
	 * @return View
	 */
	public function show(Request $request, $name)
	{
		$row = StorageResource::findByName($name);

		if (!$row)
		{
			abort(404);
		}

		app('pathway')
			->append(
				trans('storage::storage.module name'),
				route('site.storage.index')
			)
			->append(
				$row->name,
				route('site.storage.show', ['name' => $row->name])
			);

		return view('storage::site.show', [
			'row' => $row
		]);
	}
}
