<?php

namespace App\Modules\Issues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Comment;

class CommentsController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of entries
	 *
	 * @param  int $report
	 * @param  Request  $request
	 * @return View
	 */
	public function index(int $report, Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'issues.comments', [
			'report'    => 0,
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Comment::$orderBy,
			'order_dir' => Comment::$orderDir
		]);

		if (!in_array($filters['order'], ['id', 'datetimecreated', 'userid', 'comment', 'issueid']))
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
	 * @param   Request $request
	 * @param   int  $report
	 * @return  View
	 */
	public function create(Request $request, $report)
	{
		$report = Issue::findOrFail($report);

		$row = new Comment();
		$row->contactreportid = $report->id;

		if ($fields = $request->old('fields'))
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
	 * @param   Request $request
	 * @param   int  $report
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, $report, $id)
	{
		$report = Issue::findOrFail($report);

		$row = Comment::findOrFail($id);

		if ($fields = $request->old('fields'))
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
	 * @return  RedirectResponse
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

		$row = Comment::findOrNew($id);
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
	 * @return  RedirectResponse
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
				$request->session()->flash('error', trans('global.messages.delete failed'));
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
	 * @param   int  $report
	 * @return  RedirectResponse
	 */
	public function cancel($report)
	{
		//$report = app('request')->input('fields.contactreportid');
		//$report = $report ?: app('request')->input('report');

		return redirect(route('admin.issues.comments', ['report' => $report]));
	}
}
