<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Events\QueueReading;
use Carbon\Carbon;

class AllocationResource extends JsonResource
{
	/**
	 * Transform the queue collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		event($event = new QueueReading($this->resource));

		$this->resource = $event->queue;

		$data = parent::toArray($request);

		$now = Carbon::now();

		/*$data['resource'] = $this->resource()->get()->first();
		$data['resource']['api'] = route('api.resources.read', ['id' => $data['resource']['id']]);

		$data['subresource'] = $this->subresource;
		$data['subresource']['api'] = route('api.resources.subresources.read', ['id' => $data['subresource']['id']]);

		$data['schedulerpolicy'] = $this->schedulerPolicy;
		$data['schedulerpolicy']['api'] = route('api.queues.schedulerpolicies.read', ['id' => $data['schedulerpolicy']['id']]);*/

		/*if ($this->scheduler)
		{
			$data['scheduler']['api'] = route('api.queues.schedulers.read', ['id' => $data['scheduler']['id']]);
		}

		$data['sizes'] = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				if (!$item->hasEnd())
				{
					$item->datetimestop = null;
				}
				$item->api = route('api.queues.sizes.read', ['id' => $item->id]);
			});

		$data['priorsizes'] = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNotNull('datetimestop')
					->where('datetimestop', '!=', '0000-00-00 00:00:00')
					->where('datetimestop', '<=', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.sizes.read', ['id' => $item->id]);
			});

		$data['loans'] = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				if (!$item->hasEnd())
				{
					$item->datetimestop = null;
				}
				$item->api = route('api.queues.loans.read', ['id' => $item->id]);
			});

		$data['priorloans'] = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNotNull('datetimestop')
					->where('datetimestop', '!=', '0000-00-00 00:00:00')
					->where('datetimestop', '<=', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.loans.read', ['id' => $item->id]);
			});

		$data['users'] = $this->users()
			->withTrashed()
			->whereIsActive()
			->orderBy('id', 'asc')
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.users.read', ['id' => $item->id]);
			});

		$data['priorusers'] = $this->users()
			->onlyTrashed()
			->where('datetimeremoved', '!=', '0000-00-00 00:00:00')
			->orderBy('id', 'asc')
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.users.read', ['id' => $item->id]);
			});

		$data['walltimes'] = $this->walltimes
			->each(function($item, $key)
			{
				$item->api = route('api.queues.walltimes.read', ['id' => $item->id]);
			});

		$data['totalcores']  = $this->totalcores;
		$data['totalnodes']  = $this->totalnodes;
		$data['soldcores']   = $this->soldcores;
		$data['soldnodes']   = $this->soldnodes;
		$data['loanedcores'] = $this->loanedcores;
		$data['loanednodes'] = $this->loanednodes;*/
		$data['corecount']    = $this->totalcores;
		$data['nodecount']    = $this->totalnodes;
		$data['active']       = $this->active;
		if (!isset($data['serviceunits']))
		{
			$data['serviceunits'] = $this->totalserviceunits;
		}

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}

		if (!$this->hasLastSeenTime())
		{
			$data['datetimelastseen'] = null;
		}

		$data['draindown'] = 0;
		if ($this->scheduler->hasDraindownTime())
		{
			$data['draindown'] = 1;
		}
		$data['defaultnodeaccesspolicy'] = $this->scheduler->policy->code;
		$data['nodeaccesspolicy'] = $this->schedulerPolicy->code;

		$data['draindown_timeremaining'] = 0;
		$timeremaining = $this->scheduler->datetimedraindown->timestamp - $now->timestamp;
		if ($timeremaining > 0)
		{
			$data['draindown_timeremaining'] = $timeremaining;
		}

		$data['scheduler'] = $this->scheduler;

		if (!$this->scheduler->isTrashed())
		{
			$data['scheduler']['datetimeremoved'] = null;
		}

		if (!$this->scheduler->hasDraindownTime())
		{
			$data['scheduler']['datetimedraindown'] = null;
		}

		if (!$this->scheduler->hasLastImportStartTime())
		{
			$data['scheduler']['datetimelastimportstart'] = null;
		}

		if (!$this->scheduler->hasLastImportStopTime())
		{
			$data['scheduler']['datetimelastimportstop'] = null;
		}

		$data['walltime'] = $this->walltime;

		$members = $this->activeUsers;

		$userids = $members->pluck('userid')->toArray();

		if ($this->group)
		{
			$managers = $this->group->managers;
			foreach ($managers as $manager)
			{
				if ($manager->datecreated > $now->toDateTimeString())
				{
					continue;
				}

				if (in_array($manager->userid, $userids))
				{
					continue;
				}

				$members->push($manager);
			}
		}

		$data['usernames'] = $members->map(function($member)
			{
				$member->username = $member->user ? $member->user->username : '';
				return $member;
			})
			->pluck('username')
			->sort()
			->toArray();

		return $data;
	}
}
