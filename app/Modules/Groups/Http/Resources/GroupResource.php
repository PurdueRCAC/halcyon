<?php

namespace App\Modules\Groups\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Events\GroupReading;

/**
 * @mixin \App\Modules\Groups\Models\Group
 */
class GroupResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		if (!$request->input('minimal'))
		{
			event($event = new GroupReading($this->resource));

			$this->resource = $event->group;
		}

		$data = parent::toArray($request);

		$data['api'] = route('api.groups.read', ['id' => $this->id]);

		$data['departments'] = $this->departments->each(function($item, $key)
		{
			$item->api = route('api.groups.departments.read', ['id' => $item->id]);
		});

		$data['fields_of_science'] = $this->fieldsofscience->each(function($item, $key)
		{
			$item->api = route('api.groups.fieldsofscience.read', ['id' => $item->id]);
		});

		if (!$request->input('minimal'))
		{
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
					$ma['username'] = ($m->user ? $m->user->username : trans('global.unknown'));
					$ma['name'] = ($m->user ? $m->user->name : trans('global.unknown'));

					if (!$m->trashed() && ($m->user && $m->user->trashed()))
					{
						$ma['dateremoved'] = $m->user->dateremoved;
					}

					if (!$m->trashed())
					{
						$ma['dateremoved'] = null;
					}

					if ($m->isManager())
					{
						if ($m->trashed() || ($m->user && $m->user->trashed()))
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
						if ($m->trashed() || ($m->user && $m->user->trashed()))
						{
							$data['priorviewers'][] = $ma;
						}
						else
						{
							$data['viewers'][] = $ma;
						}
						continue;
					}

					if ($m->trashed() || ($m->user && $m->user->trashed()))
					{
						$data['priormembers'][] = $ma;
					}
					else
					{
						$data['members'][] = $ma;
					}
				}
			}

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
					$item->name = ($item->user ? $item->user->name : trans('global.unknown'));
					$item->username = ($item->user ? $item->user->username : trans('global.unknown'));
					$item->setHidden(['user']);
					$item->api = route('api.unixgroups.members.read', ['id' => $item->id]);
				});
			});

			$data['priorunixgroups'] = $this->unixgroups()
				->onlyTrashed()
				->get()
				->each(function ($item, $key)
				{
					$item->api = route('api.unixgroups.read', ['id' => $item->id]);
					$item->members->each(function($item, $key)
					{
						$item->name = ($item->user ? $item->user->name : trans('global.unknown'));
						$item->username = ($item->user ? $item->user->username : trans('global.unknown'));
						$item->setHidden(['user']);
						$item->api = route('api.unixgroups.members.read', ['id' => $item->id]);
					});
				});
		}

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$managerids = $this->managers->pluck('userid')->toArray();
			$managerids[] = $this->owneruserid;

			$data['can']['create'] = $user->can('create groups');
			$data['can']['edit']   = ($user->can('edit groups') || ($user->can('edit.own groups') && in_array($user->id, $managerids)));
			$data['can']['delete'] = $user->can('delete groups');
			$data['can']['manage'] = $user->can('manage groups');
			$data['can']['admin']  = $user->can('admin groups');
		}

		return $data;
	}
}
