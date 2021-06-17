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
			$data['members']  = array();
			$data['viewers']  = array();

			$data['priormanagers'] = array();
			$data['priormembers']  = array();
			$data['priorviewers']  = array();

			foreach ($this->members()->withTrashed()->get() as $m)
			{
				$m->api = route('api.groups.members.read', ['id' => $m->id]);

				$ma = $m->toArray();

				if (!$m->isTrashed() && ($m->user && $m->user->isTrashed()))
				{
					$ma['dateremoved'] = $m->user->dateremoved;
				}

				if (!$m->isTrashed())
				{
					$ma['dateremoved'] = null;
				}

				if ($m->isManager())
				{
					if ($m->isTrashed() || ($m->user && $m->user->isTrashed()))
					{
						$data['priormanagers'][] = $ma;
					}
					else
					{
						$data['managers'][] = $ma;
					}
					continue;
				}

				if ($m->isViewer())
				{
					if ($m->isTrashed() || ($m->user && $m->user->isTrashed()))
					{
						$data['priorviewers'][] = $ma;
					}
					else
					{
						$data['viewers'][] = $ma;
					}
					continue;
				}

				if ($m->isTrashed() || ($m->user && $m->user->isTrashed()))
				{
					$data['priormembers'][] = $ma;
				}
				else
				{
					$data['members'][] = $ma;
				}
			}
		}

		$data['department'] = $this->departments->each(function($item, $key)
		{
			$item->api = route('api.groups.departments.read', ['id' => $item->id]);
		});

		$data['motd'] = $this->motd;
		if ($data['motd'])
		{
			$data['motd']['api'] = route('api.groups.motd.read', ['id' => $data['motd']['id']]);
		}

		$data['loans'] = $this->loans()
			->whenAvailable()
			->where('groupid', '=', $this->id)
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.storage.loans.read', ['id' => $item->id]);
			});

		$data['priorloans'] = $this->loans()
			->whenNotAvailable()
			->where('groupid', '=', $this->id)
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.storage.loans.read', ['id' => $item->id]);
			});

		$data['purchases'] = $this->purchases()
			->whenAvailable()
			->where('groupid', '=', $this->id)
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.storage.purchases.read', ['id' => $item->id]);
			});

		$data['priorpurchases'] = $this->purchases()
			->whenNotAvailable()
			->where('groupid', '=', $this->id)
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.storage.purchases.read', ['id' => $item->id]);
			});

		$data['directories'] = $this->directories->each(function ($item, $key)
		{
			$item->api = route('api.storage.directories.read', ['id' => $item->id]);
			$item->messages = $item->messages()
				->where('userid', '<>', 0)
				->orderBy('datetimesubmitted', 'desc')
				->get()
				->each(function($item, $key)
				{
					$item->api = route('api.messages.read', ['id' => $item->id]);
				});
		});

		/*$data['resources'] = $this->resources->each(function ($item, $key)
		{
			$item->api = route('api.resources.read', ['id' => $item->id]);
		});*/

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
			$buckets[$k]['unallocatedbytes'] = abs($buckets[$k]['allocatedbytes'] - $buckets[$k]['totalbytes']);
		}

		$data['storagebuckets'] = $buckets;

		$data['priordirectories'] = $this->directories()
			->onlyTrashed()
			->where('datetimeremoved', '!=', '0000-00-00 00:00:00')
			->get()
			->each(function ($item, $key)
			{
				$item->api = route('api.storage.directories.read', ['id' => $item->id]);
				$item->messages = $item->messages()
					->where('userid', '<>', 0)
					->orderBy('datetimesubmitted', 'desc')
					->get()
					->each(function($item, $key)
					{
						$item->api = route('api.messages.read', ['id' => $item->id]);
					});
			});

		$data['unixgroups'] = $this->unixgroups->each(function ($item, $key)
		{
			$item->api = route('api.unixgroups.read', ['id' => $item->id]);
			$item->members->each(function($item, $key)
			{
				$item->api = route('api.unixgroups.members.read', ['id' => $item->id]);
			});
		});

		$data['priorunixgroups'] = $this->unixgroups()
			->onlyTrashed()
			->where('datetimeremoved', '!=', '0000-00-00 00:00:00')
			->get()
			->each(function ($item, $key)
			{
				$item->api = route('api.unixgroups.read', ['id' => $item->id]);
				$item->members->each(function($item, $key)
				{
					$item->api = route('api.unixgroups.members.read', ['id' => $item->id]);
				});
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
