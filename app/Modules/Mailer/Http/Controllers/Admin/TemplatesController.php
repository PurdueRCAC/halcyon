<?php

namespace App\Modules\Mailer\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Users\Models\User;
use App\Modules\History\Models\Log;
use App\Modules\Mailer\Models\Message;
use App\Modules\Mailer\Mail\GenericMessage;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Access\Map;
use Carbon\Carbon;

class TemplatesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Message::$orderBy,
			'order_dir' => Message::$orderDir,
		);

		$reset = false;
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('users.messages.filter_' . $key)
			 && $request->input($key) != session()->get('users.messages.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('users.messages.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
						->orWhere('body', 'like', '%' . strtolower((string)$filters['search']) . '%');
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
	 * @return  View
	 */
	public function create()
	{
		$row = new Message();

		if ($fields = app('request')->old('fields'))
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
	 * @param   integer  $id
	 * @return  View
	 */
	public function edit($id)
	{
		$row = Message::findOrFail($id);

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		return view('mailer::admin.templates.edit', [
			'row' => $row
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
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

		$row = $id ? Message::findOrFail($id) : new Message();
		$row->subject = $request->input('subject');
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
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.mailer.templates'));
	}
}
