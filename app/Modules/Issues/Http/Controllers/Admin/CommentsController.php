<?php

namespace App\Modules\Issues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Comment;

class CommentsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index($report, StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'report'    => 0,
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Comment::$orderBy,
			'order_dir' => Comment::$orderDir
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('issues.comments.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Comment)->getAttributes())))
		{
			$filters['order'] = Comment::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Comment::$orderDir;
		}

		$report = Issue::findOrFail($report);

		$query = $report->comments();

		if ($filters['search'])
		{
			$query->where('comment', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('issues::admin.comments.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'report'  => $report
		]);
	}

	/**
	 * Show the form for creating a new report
	 *
	 * @param   integer  $report
	 * @return  Response
	 */
	public function create($report)
	{
		$report = Issue::findOrFail($report);

		$row = new Comment();
		$row->contactreportid = $report->id;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::admin.comments.edit', [
			'row'    => $row,
			'report' => $report
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $report
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($report, $id)
	{
		$report = Issue::findOrFail($report);

		$row = Comment::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::admin.comments.edit', [
			'row'     => $row,
			'report' => $report
		]);
	}

	/**
	 * Store an entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.comment'   => 'required',
			'fields.contactreportid' => 'required'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Comment::findOrFail($id) : new Comment();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->with('error', $id ? 'Failed to create item.' : 'Failed to update item.');
		}

		return redirect(route('admin.issues.comments', ['report' => $row->contactreportid]))
			->withSuccess('Item created!');
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Comment::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', $success));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @param   integer  $report
	 * @return  Response
	 */
	public function cancel($report)
	{
		//$report = app('request')->input('fields.contactreportid');
		//$report = $report ?: app('request')->input('report');

		return redirect(route('admin.issues.comments', ['report' => $report]));
	}
}
