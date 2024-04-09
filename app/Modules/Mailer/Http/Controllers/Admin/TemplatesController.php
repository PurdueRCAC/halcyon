<?php

namespace App\Modules\Mailer\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Users\Models\User;
use App\Modules\History\Models\Log;
use App\Modules\Mailer\Models\Message;
use App\Modules\Mailer\Mail\GenericMessage;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Halcyon\Access\Map;
use Carbon\Carbon;

class TemplatesController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'mailer.templates', [
			'search'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Message::$orderBy,
			'order_dir' => Message::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'subject', 'body', 'state', 'access', 'category_id']))
		{
			$filters['order'] = Message::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Message::$orderDir;
		}

		$query = Message::query()
			->where('template', '=', 1);

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$where->where('subject', 'like', '%' . strtolower((string)$filters['search']) . '%')
						->orWhere('body', 'like', '%' . strtolower((string)$filters['search']) . '%')
						->orWhere('name', 'like', '%' . strtolower((string)$filters['search']) . '%');
				});
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('mailer::admin.templates.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Message();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('mailer::admin.templates.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, int $id)
	{
		$row = Message::findOrFail($id);

		if ($fields = $request->old())
		{
			$row->fill($fields);
		}

		return view('mailer::admin.templates.edit', [
			'row' => $row
		]);
	}

	/**
	 * Copy the specified entry to the edit form to make a new entry.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return View
	 */
	public function copy(Request $request, int $id)
	{
		$row = Message::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$row->id = null;
		if (!$row->name)
		{
			$row->name = $row->subject;
		}
		$row->subject .= ' (copy)';
		$row->name .= ' (copy)';

		return view('mailer::admin.templates.edit', [
			'row'  => $row
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'name' => 'nullable|string|max:255',
			'subject' => 'required|string|max:255',
			'body' => 'required|string|max:15000',
			'alert' => 'nullable|string|max:50',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Message::findOrNew($id);
		$row->subject = $request->input('subject');
		$row->name = $row->subject;
		if ($request->has('name'))
		{
			$row->name = $request->input('name');
		}
		$row->body = $request->input('body');
		if ($request->has('alert'))
		{
			$row->alert = $request->input('alert');
		}
		$row->template = 1;

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
			// Message: This is recursive and will also remove all descendents
			$row = Message::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.mailer.templates'));
	}
}
