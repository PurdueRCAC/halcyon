<?php

namespace App\Modules\Storage\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Storage\Models\StorageResource;

class StorageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
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
	 * @return Response
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

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit()
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

	/**
	 * Update the specified resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function update(Request $request)
	{
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
	 */
	public function destroy()
	{
	}
}
