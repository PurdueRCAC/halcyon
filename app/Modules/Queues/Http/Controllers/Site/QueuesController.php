<?php

namespace App\Modules\Queues\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Entities\Queue;

class QueuesController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * @return Response
	 */
	public function index()
	{
		$rows = Queue::paginate(20);

		app('pathway')->append(
			config('queues.name'),
			url('/queues')
		);

		return view('queues::site.index', ['rows' => $rows]);
	}

	/**
	 * Show the form for creating a new queue.
	 * @return Response
	 */
	public function create()
	{
		app('pathway')
			->append(
				config('queues.name'),
				url('/queues')
			)
			->append(
				__('queues::queues.create'),
				url('/queues/new')
			);

		return view('queues::site.create');
	}

	/**
	 * Store a newly created queue in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
	}

	/**
	 * Show the specified queue.
	 * @return Response
	 */
	public function show()
	{
		$id = 1;

		app('pathway')
			->append(
				config('queues.name'),
				url('/queues')
			)
			->append(
				__('queues::queues.show'),
				url('/queues/:id', $id)
			);

		return view('queues::site.show');
	}

	/**
	 * Show the form for editing the specified queue.
	 * @return Response
	 */
	public function edit()
	{
		$id = 1;

		app('pathway')
			->append(
				config('queues.name'),
				url('/queues')
			)
			->append(
				__('queues::queues.edit'),
				url('/queues/edit/:id', $id)
			);

		return view('queues::site.edit');
	}

	/**
	 * Update the specified queue in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function update(Request $request)
	{
	}

	/**
	 * Remove the specified queue from storage.
	 * @return Response
	 */
	public function destroy()
	{
	}
}
