<?php

namespace App\Modules\Messages\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Messages\Models\Message;
use App\Modules\Messages\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class MessagesController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'state'     => 'published',
			'start'     => null,
			'stop'      => null,
			'type'      => '',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Message::$orderBy,
			'order_dir' => Message::$orderDir,
			'type'      => null,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('messages.index.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Message)->getAttributes())))
		{
			$filters['order'] = Message::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Message::$orderDir;
		}

		$query = Message::query()
			->with('type');

		if ($filters['state'] == 'complete')
		{
			$query->where('datetimestarted', '!=', '0000-00-00 00:00:00')
				->where('datetimecompleted', '!=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'incomplete')
		{
			$query->where('datetimestarted', '!=', '0000-00-00 00:00:00')
				->where('datetimecompleted', '=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'pending')
		{
			$query->where('datetimestarted', '=', '0000-00-00 00:00:00')
				->where('datetimecompleted', '=', '0000-00-00 00:00:00');
		}

		if ($filters['start'])
		{
			$query->where('datetimesubmitted', '>', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('datetimesubmitted', '<=', $filters['stop']);
		}

		if ($filters['type'])
		{
			$query->where('messagequeuetypeid', '=', $filters['type']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('messages::admin.messages.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Message();

		$types = Type::orderBy('name', 'asc')->get();

		foreach ($types as $type)
		{
			if ($type->id == config('modules.news.default_type', 0))
			{
				$row->newstypeid = $type->id;
			}
		}

		return view('messages::admin.messages.edit', [
			'row'   => $row,
			'types' => $types
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
		$request->validate([
			'fields.headline' => 'required',
			'fields.body' => 'required'
		]);

		$row = new Message($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		return $this->cancel()->withSuccess('Item created!');
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Message::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();

		return view('messages::admin.messages.edit', [
			'row'   => $row,
			'types' => $types
		]);
	}

	/**
	 * Update the specified entry
	 *
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'fields.headline' => 'required'
		]);

		$fields = $request->input('fields');
		$fields['location'] = (string)$fields['location'];

		$row = Message::findOrFail($id);
		$row->fill($fields);

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('messages.update failed'));
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request  $request
	 * @param  integer  $id
	 * @return Response
	 */
	public function state(Request $request, $id)
	{
		$action = $request->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'news::news.select to publish' : 'news::news.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Message::findOrFail(intval($id));

			if ($row->published == $state)
			{
				continue;
			}

			// Don't update last modified timestamp for state changes
			$row->timestamps = false;

			$row->published = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'news::news.items published'
				: 'news::news.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @param  Request  $request
	 * @return  Response
	 */
	public function destroy(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Message::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', $success));
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
		return redirect(route('admin.messages.index'));
	}
}
