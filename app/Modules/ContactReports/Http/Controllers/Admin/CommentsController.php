<?php

namespace App\Modules\ContactReports\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Comment;

class CommentsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @param  integer  $report
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('crm.comments.filter_' . $key)
			 && $request->input($key) != session()->get('crm.comments.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('crm.comments.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'comment', 'datetimecreated', 'userid']))
		{
			$filters['order'] = Comment::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Comment::$orderDir;
		}

		$report = Report::findOrFail($report);

		$query = $report->comments();

		if ($filters['search'])
		{
			$query->where('comment', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('contactreports::admin.comments.index', [
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
		$report = Report::findOrFail($report);

		$row = new Comment();
		$row->contactreportid = $report->id;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('contactreports::admin.comments.edit', [
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
		$report = Report::findOrFail($report);

		$row = Comment::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('contactreports::admin.comments.edit', [
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
		//$request->validate([
		$rules = [
			'fields.comment'         => 'required|string|max:8096',
			'fields.contactreportid' => 'required|integer'
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

		return redirect(route('admin.contactreports.comments', ['report' => $row->contactreportid]))
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
		return redirect(route('admin.contactreports.comments', ['report' => $report]));
	}
}
