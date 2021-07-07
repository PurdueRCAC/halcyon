<?php

namespace App\Modules\Messages\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Messages\Models\Message;
use App\Modules\Messages\Models\Type;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

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
		$weekago = Carbon::now()->modify('-1 week');

		// Get filters
		$filters = array(
			'state'     => 'published',
			'status'    => '*',
			'start'     => $weekago->format('Y-m-d'),
			'stop'      => null,
			'type'      => '',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Message::$orderBy,
			'order_dir' => Message::$orderDir,
			'type'      => null,
		);

		$reset = false;
		foreach ($filters as $key => $default)
		{
			if ($key != 'page' && session()->get('messages.index.filter_' . $key) != $request->mergeWithBase()->input($key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('messages.index.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
			$query->where('datetimesubmitted', '>', $filters['start'] . ' 00:00:00');
		}

		if ($filters['status'] == 'success')
		{
			$query->where('returnstatus', '=', 0);
		}
		elseif ($filters['status'] == 'failure')
		{
			$query->where('returnstatus', '>', 0);
		}

		if ($filters['stop'])
		{
			$query->where('datetimesubmitted', '<=', $filters['stop'] . ' 00:00:00');
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

		$stats = new \stdClass;
		$stats->failed = Message::query()
			->whereStarted()
			->whereCompleted($filters['start'])
			->whereNotSuccessful()
			->count();
	
		$stats->succeeded = Message::query()
			->whereStarted()
			->whereCompleted($filters['start'])
			->whereSuccessful()
			->count();

		$stats->pending = Message::query()
			->where('datetimecompleted', '=', '0000-00-00 00:00:00')
			->count();

		return view('messages::admin.messages.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
			'stats'   => $stats
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
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
			return redirect()->back()->withError(trans('global.messages.update failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
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
			$request->session()->flash('warning', trans('news::news.select to ' . ($state ? 'publish' : 'unpublish')));
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
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Rerun the specified messages
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function rerun(Request $request)
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

			if (!$row->completed())
			{
				continue;
			}

			if (!$row->update([
				'datetimestarted' => null,
				'datetimecompleted' => null,
				'pid' => 0,
				'returnstatus' => 0
			]))
			{
				$request->session()->flash('error', trans('messages::messages.error.failed to rerun'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages::messages.item reset', ['count' => $success]));
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
