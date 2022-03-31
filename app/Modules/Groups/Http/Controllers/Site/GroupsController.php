<?php

namespace App\Modules\Groups\Http\Controllers\Site;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\FieldOfScience;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Department;
use App\Modules\Groups\Models\GroupDepartment;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\Type;
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
			'fields.name' => 'required|max:255',
			'fields.unixgroup' => 'nullable|max:10',
			'fields.cascademanagers' => 'nullable|integer',
		]);

		$id = $request->input('id');

		$row = $id ? Group::findOrFail($id) : new Group;
		$row->fill($request->input('fields'));
		//$row->slug = $row->normalize($row->name);
		if (!$request->has('fields.cascademanagers') || !$request->input('fields.cascademanagers'))
		{
			$row->cascademanagers = 0;
		}
		else
		{
			$row->cascademanagers = 1;
		}

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
	 * Import
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function import(Request $request)
	{
		$id = $request->input('id');

		$group = Group::findOrFail($id);

		$disk = 'local';
		$files = $request->file();

		if (empty($files))
		{
			abort(415, trans('groups::groups.error.no files'));
		}

		$response = [
			'data' => array(),
			'error' => array()
		];

		//$row = 0;
		//$headers = array();
		//$data = array();

		$fileNotUploaded = false;
		$maxSize = config('module.courses.max-file-size', 0);
		$allowedTypes = config('module.courses.allowed-extensions', ['csv', 'xlsx', 'ods']);

		foreach ($files as $file)
		{
			// Check file size
			if (($maxSize && $file->getSize() / 1024 > $maxSize)
			 || $file->getSize() / 1024 > $file->getMaxFilesize())
			{
				$fileNotUploaded = true;
				continue;
			}

			$extension = $file->getClientOriginalExtension();

			// Check allowed file type
			// Doing this by file extension is iffy at best but
			// detection by contents produces `txt`
			if (!empty($allowedTypes)
			 && !in_array($extension, $allowedTypes))
			{
				$fileNotUploaded = true;
				continue;
			}

			// Save file
			$path = $file->store('temp');

			try
			{
				// Get file data and process into a collection of objects
				/*$handle = fopen(storage_path('app/' . $path), 'r');

				if ($handle !== false)
				{
					while (!feof($handle))
					{
						$line = fgetcsv($handle, 0, ',');

						if ($row == 0)
						{
							$headers = $line;
							$row++;
							continue;
						}

						$item = new Fluent;
						foreach ($headers as $k => $v)
						{
							$v = strtolower($v);

							$item->{$v} = $line[$k];
						}

						$data[] = $item;

						$row++;
					}
					fclose($handle);
				}*/
				$path = storage_path('app/' . $path);

				if ($extension == 'csv' || $extension == 'txt')
				{
					// CSV files get read as plain text and throw:
					//     Box\Spout\Common\Exception\UnsupportedTypeException
					//     No readers supporting the given type: txt
					$reader = ReaderEntityFactory::createCSVReader();
				}
				else
				{
					$reader = ReaderEntityFactory::createReaderFromFile($path);
				}
				$reader->open($path);

				foreach ($reader->getSheetIterator() as $sheet)
				{
					foreach ($sheet->getRowIterator() as $i => $row)
					{
						// do stuff with the row
						$cells = $row->getCells();

						if (empty($headers))
						{
							foreach ($cells as $j => $cell)
							{
								$headers[] = trim($cell->getValue());
							}

							continue;
						}

						$item = new Fluent;
						foreach ($headers as $k => $v)
						{
							$v = strtolower($v);
							$item->{$v} = $cells[$k]->getValue();
						}

						$data[] = $item;
					}
				}

				$reader->close();

				$data = collect($data);

				foreach ($data as $item)
				{
					if (!$item->username
					 && !$item->email)
					{
						continue;
					}

					// See if an account already exists
					// Create if not
					if (!$item->username && $item->email)
					{
						$item->username = strstr($item->email, '@', true);
					}

					$user = User::createFromUsername($item->username);

					if (!$user || !$user->id)
					{
						// Something went wrong
						throw new \Exception(trans('groups::groups.error.entry failed for user', ['name' => $item->username]));
					}

					// See if membership already exists
					$member = Member::query()
						->withTrashed()
						->where('userid', '=', $user->id)
						->where('groupid', '=', $group->id)
						->first();

					if ($member)
					{
						// Was apart of the class but membership was removed?
						if ($member->trashed())
						{
							// Restore membership
							$member->restore();
						}

						// Already apart of the class
						continue;
					}

					$membertype = Type::MEMBER;

					if (isset($item->membership))
					{
						if (is_numeric($item->membership))
						{
							$membertype = $item->membership;
						}
						else
						{
							$item->membership = strtoupper(trim($item->membership));

							if (in_array($item->type, ['MEMBER', 'MANAGER', 'PENDING', 'VIEWER']))
							{
								$t = $item->membership;
								$membertype = Type::$t;
							}
						}
					}

					// Create the membership
					$member = new Member;
					$member->groupid = $group->id;
					$member->userid = $user->id;
					$member->membertype = $membertype;
					$member->save();

					foreach (get_object_vars($item) as $key => $val)
					{
						$key = strtolower($key);

						if (in_array($key, ['name', 'username', 'email', 'membership']))
						{
							continue;
						}

						$val = strtolower($val);

						if (!$val || $val == 'no' || $val == '0' || $val == 'false')
						{
							continue;
						}

						if (preg_match('/([a-z0-9\-_]+) \(([^\)]+)\)/', $val, $matches))
						{
							$resource = Asset::findByName($matches[2]);

							if (!$resource)
							{
								$response['error'][] = 'Could not find resource "' . $matches[2] . '"';
								continue;
							}

							$queue = $group->queues()
								->where('name', '=', $matches[1])
								->whereIn('subresourceid', $resource->subresources->pluck('id')->toArray())
								->first();

							if (!$queue)
							{
								$response['error'][] = 'Could not find queue "' . $matches[1] . '"';
								continue;
							}

							$queue->addUser($user->id);
						}
						else
						{
							$unix = $group->unixGroups()
								->where('name', '=', $val)
								->first();

							if (!$unix)
							{
								$response['error'][] = 'Could not find unix group "' . $val . '"';
								continue;
							}

							$unix->addMember($user->id);
						}
					}

					$response['data'][] = $member->toArray();
				}
			}
			catch (\Exception $e)
			{
				$response['error'][] = $e->getMessage();
				$fileNotUploaded = false;
			}

			// Clean up
			Storage::disk($disk)->delete($path);
		}

		if ($fileNotUploaded)
		{
			$response['message'] = trans('groups::groups.error.not all uploaded');
		}

		return redirect(route('site.users.account'));
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
