<?php

namespace App\Modules\Dashboard\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Dashboard\Entities\Listener;

class DashboardController extends Controller
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
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				__('resources::assets.create'),
				url('/resources/new')
			);

		return view('resources::site.create');
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
	}

	/**
	 * Show the specified resource.
	 * @return Response
	 */
	public function show()
	{
		$id = 1;

		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				__('resources::assets.show'),
				url('/resources/:id', $id)
			);

		return view('resources::site.show');
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
