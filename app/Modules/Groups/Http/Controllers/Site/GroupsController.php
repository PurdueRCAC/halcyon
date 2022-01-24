<?php

namespace App\Modules\Groups\Http\Controllers\Site;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\FieldOfScience;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Department;
use App\Modules\Groups\Models\GroupDepartment;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\UnixGroupMember;

class GroupsController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$user = auth()->user();

		$groups = $user->groups()
			->where('groupid', '>', 0)
			->get()
			->pluck('groupid')
			->toArray();
		$groups = array_unique($groups);

		$total = count($groups);

		$queueusers = $user->queues()
			->with('queue')
			->whereIn('membertype', [1, 4])
			->get();

		foreach ($queueusers as $qu)
		{
			if ($qu->isMember() && $qu->trashed())
			{
				continue;
			}

			$queue = $qu->queue;

			if (!$queue || $queue->trashed())
			{
				continue;
			}

			if (!$queue->scheduler || $queue->scheduler->trashed())
			{
				continue;
			}

			if (!in_array($queue->groupid, $groups))
			{
				$groups[] = $queue->groupid;
				$total++;
			}
		}

		$unixusers = UnixGroupMember::query()
			->where('userid', '=', $user->id)
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($unixusers as $uu)
		{
			if ($uu->trashed())
			{
				continue;
			}

			$unixgroup = $uu->unixgroup;

			if (!$unixgroup || $unixgroup->trashed())
			{
				continue;
			}

			if (!$unixgroup->group || $unixgroup->group->trashed())
			{
				continue;
			}

			if (!in_array($unixgroup->groupid, $groups))
			{
				$groups[] = $unixgroup->groupid;
				$total++;
			}
		}

		$rows = $user->groups()
			->where('groupid', '>', 0)
			->orderBy('membertype', 'desc')
			->get();

		$groups = array_unique($rows->pluck('groupid')->toArray());

		foreach ($queueusers as $qu)
		{
			if ($qu->isMember() && $qu->trashed())
			{
				continue;
			}

			$queue = $qu->queue;

			if (!$queue || $queue->trashed())
			{
				continue;
			}

			if (!$queue->scheduler || $queue->scheduler->trashed())
			{
				continue;
			}

			if (!in_array($queue->groupid, $groups))
			{
				$qu->groupid = $queue->groupid;

				$rows->add($qu);

				$groups[] = $queue->groupid;
			}
		}

		foreach ($unixusers as $uu)
		{
			if ($uu->trashed())
			{
				continue;
			}

			$unixgroup = $uu->unixgroup;

			if (!$unixgroup || $unixgroup->trashed())
			{
				continue;
			}

			if (!$unixgroup->group)
			{
				continue;
			}

			if (!in_array($unixgroup->groupid, $groups))
			{
				$uu->groupid = $unixgroup->groupid;
				$uu->group = $unixgroup->group;
				$rows->add($uu);
				$groups[] = $unixgroup->groupid;
			}
		}

		$managers = $rows->filter(function($value, $key)
		{
			return $value->isManager();
		});//->pluck('groupid')->toArray();

		foreach ($rows as $k => $g)
		{
			foreach ($managers as $manager)
			{
				if ($g->groupid == $manager->groupid && $g->id != $manager->id)
				{
					$rows->forget($k);
				}
			}
		}

		return view('groups::site.index', [
			'rows'    => $rows,
			'user' => $user,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Group();

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Group::findOrFail($id) : new Group();
		$row->fill($request->input('fields'));
		$row->slug = $row->normalize($row->name);

		if (!$row->created_by)
		{
			$row->created_by = auth()->user()->id;
		}

		if (!$row->updated_by)
		{
			$row->updated_by = auth()->user()->id;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  integer  $id
	 * @return Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Group::findOrFail($id);

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
			'fields' => $fields,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Group::findOrFail($id);

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
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function export(Request $request)
	{
		$filename = $request->input('filename', 'data') . '.csv';

		$headers = array(
			'Content-type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0'
		);

		if ($data = $request->input('data', '[]'))
		{
			$data = json_decode(urldecode($data));
		}

		$callback = function() use ($data)
		{
			$file = fopen('php://output', 'w');

			if (is_array($data))
			{
				foreach ($data as $datum)
				{
					fputcsv($file, $datum);
				}
			}
			fclose($file);
		};

		return response()->streamDownload($callback, $filename, $headers);
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.groups.index'));
	}
}
