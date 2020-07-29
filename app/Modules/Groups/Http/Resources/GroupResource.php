<?php

namespace App\Modules\Groups\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Events\GroupReading;

class GroupResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		event($event = new GroupReading($this->resource));

		$this->resource = $event->group;

		$data = parent::toArray($request);

		$data['api'] = route('api.groups.read', ['id' => $this->id]);

		if (auth()->user() && auth()->user()->can('manage groups'))
		{
			$data['managers'] = array();
			$data['members'] = array();
			$data['viewers'] = array();

			$data['priormanagers'] = array();
			$data['priormembers'] = array();
			$data['priorviewers'] = array();

			foreach ($this->members()->withTrashed()->get() as $m)
			{
				if ($m->isManager())
				{
					if ($m->dateremoved
					 && $m->dateremoved != '-0001-11-30 00:00:00'
					 && $m->dateremoved != '0000-00-00 00:00:00')
					{
						$data['priormanagers'][] = $m;
					}
					else
					{
						$data['managers'][] = $m;
					}
					continue;
				}

				if ($m->isViewer())
				{
					if ($m->trashed()
					 && $m->dateremoved != '-0001-11-30 00:00:00'
					 && $m->dateremoved != '0000-00-00 00:00:00')
					{
						$data['priorviewers'][] = $m;
					}
					else
					{
						$data['viewers'][] = $m;
					}
					continue;
				}

				if ($m->trashed()
				 && $m->dateremoved != '-0001-11-30 00:00:00'
				 && $m->dateremoved != '0000-00-00 00:00:00')
				{
					$data['priormembers'][] = $m;
				}
				else
				{
					$data['members'][] = $m;
				}
			}
			//$data['members'] = $this->members;
		}
		$data['department'] = $this->departmentList;
		$data['motd'] = $this->motd;
		$data['loans'] = $this->loans()->whenAvailable()->where('groupid', '=', $this->id)->get();
		$data['priorloans'] = $this->loans()->whenNotAvailable()->where('groupid', '=', $this->id)->get();
		$data['purchases'] = $this->purchases()->whenAvailable()->where('groupid', '=', $this->id)->get();
		$data['priorpurchases'] = $this->purchases()->whenNotAvailable()->where('groupid', '=', $this->id)->get();
		$data['directories'] = $this->directories->each(function ($item, $key)
		{
			$item->messages;
			//$item->permissions = $item->unixPermissions;
		});

		$data['resources'] = $this->resources;

		$buckets = array();
		foreach ($this->loans as $dir)
		{
			if (!isset($buckets[$dir->resourceid]))
			{
				$buckets[$dir->resourceid] = array(
					'resource' => 0,
					'soldbytes' => 0,
					'loanedbytes' => 0,
					'totalbytes' => 0,
					'unallocatedbytes' => 0,
					'allocatedbytes' => 0,
				);
			}
			$buckets[$dir->resourceid]['resource'] = $dir->resourceid;
			$buckets[$dir->resourceid]['loanedbytes'] += $dir->bytes;
			$buckets[$dir->resourceid]['totalbytes'] += $dir->bytes;
		}
		foreach ($this->purchases as $dir)
		{
			if (!isset($buckets[$dir->resourceid]))
			{
				$buckets[$dir->resourceid] = array(
					'resource' => 0,
					'soldbytes' => 0,
					'loanedbytes' => 0,
					'totalbytes' => 0,
					'unallocatedbytes' => 0,
					'allocatedbytes' => 0,
				);
			}
			$buckets[$dir->resourceid]['resource'] = $dir->resourceid;
			$buckets[$dir->resourceid]['soldbytes'] += $dir->bytes;
			$buckets[$dir->resourceid]['totalbytes'] += $dir->bytes;
		}
		foreach ($this->directories as $dir)
		{
			if (!isset($buckets[$dir->resourceid]))
			{
				continue;
			}
			$buckets[$dir->resourceid]['allocatedbytes'] += $dir->bytes;
		}
		foreach ($buckets as $k => $v)
		{
			$buckets[$k]['unallocatedbytes'] = ($buckets[$k]['allocatedbytes'] - $buckets[$k]['totalbytes']);
		}

		$data['storagebuckets'] = $buckets;

		$data['priordirectories'] = $this->directories()
			->onlyTrashed()
			->where('datetimeremoved', '!=', '0000-00-00 00:00:00')
			->get()
			->each(function ($item, $key)
			{
				$item->messages;
				//$item->permissions = $item->unixPermissions;
			});
		$data['unixgroups'] = $this->unixgroups->each(function ($item, $key)
		{
			$item->members;
		});
		$data['priorunixgroups'] = $this->unixgroups()
			->onlyTrashed()
			->where('datetimeremoved', '!=', '0000-00-00 00:00:00')
			->get()
			->each(function ($item, $key)
			{
				$item->members;
			});

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit groups') || ($user->can('edit.own groups') && $this->owneruserid == $user->id));
			$data['can']['delete'] = $user->can('delete groups');
		}

		return $data;
	}
}
