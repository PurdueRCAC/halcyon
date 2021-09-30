<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Events\QueueReading;
use Carbon\Carbon;

class QueueResource extends JsonResource
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

		$data['resource'] = $this->resource()->get()->first();
		if (!$this->subresource->isTrashed())
		{
			$data['resource']['datetimeremoved'] = null;
		}
		$data['resource']['api'] = route('api.resources.read', ['id' => $data['resource']['id']]);

		$data['subresource'] = $this->subresource;
		if (!$this->subresource->isTrashed())
		{
			$data['subresource']['datetimeremoved'] = null;
		}
		$data['subresource']['api'] = route('api.resources.subresources.read', ['id' => $data['subresource']['id']]);

		$data['schedulerpolicy'] = $this->schedulerPolicy;
		$data['schedulerpolicy']['api'] = route('api.queues.schedulerpolicies.read', ['id' => $data['schedulerpolicy']['id']]);

		$data['scheduler'] = $this->scheduler;
		if ($this->scheduler)
		{
			if (!$this->scheduler->hasDraindownTime())
			{
				$data['scheduler']['datetimedraindown'] = null;
			}
			if (!$this->scheduler->isTrashed())
			{
				$data['scheduler']['datetimeremoved'] = null;
			}
			if (!$this->scheduler->hasLastImportStartTime())
			{
				$data['scheduler']['datetimelastimportstart'] = null;
			}
			if (!$this->scheduler->hasLastImportStopTime())
			{
				$data['scheduler']['datetimelastimportstop'] = null;
			}
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
				if (!$item->hasEnd())
				{
					$item->datetimestop = null;
				}
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
				if (!$item->hasEnd())
				{
					$item->datetimestop = null;
				}
				$item->api = route('api.queues.loans.read', ['id' => $item->id]);
			});

		$data['users'] = $this->users()
			->withTrashed()
			->whereIsActive()
			->orderBy('id', 'asc')
			->get()
			->each(function($item, $key)
			{
				if (!$item->isTrashed())
				{
					$item->datetimeremoved = null;
				}
				if (!$item->wasLastseen())
				{
					$item->datetimelastseen = null;
				}
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
				if (!$item->hasEnd())
				{
					$item->datetimestop = null;
				}
				$item->api = route('api.queues.walltimes.read', ['id' => $item->id]);
			});

		$data['totalcores']  = $this->totalcores;
		$data['totalnodes']  = $this->totalnodes;
		$data['soldcores']   = $this->soldcores;
		$data['soldnodes']   = $this->soldnodes;
		$data['loanedcores'] = $this->loanedcores;
		$data['loanednodes'] = $this->loanednodes;
		$data['active']      = $this->active;

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}
		if (!$this->hasLastSeenTime())
		{
			$data['datetimelastseen'] = null;
		}

		$data['api'] = route('api.queues.read', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit queues') || ($user->can('edit.own queues') && $item->userid == $user->id));
			$data['can']['delete'] = $user->can('delete queues');
		}

		return $data;
	}
}
