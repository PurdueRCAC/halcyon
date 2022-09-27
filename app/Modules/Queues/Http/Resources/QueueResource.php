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

		$objs = $request->input('expand', 'subresource');
		$objs = explode(',', $objs);
		$objs = array_map('trim', $objs);

		if (in_array('group', $objs) || in_array('all', $objs))
		{
			$data['group'] = $this->group;
			$data['group']['api'] = route('api.groups.read', ['id' => $data['group']['id']]);
		}

		if (in_array('resource', $objs) || in_array('all', $objs))
		{
			$data['resource'] = $this->resource()->get()->first();
			$data['resource']['api'] = route('api.resources.read', ['id' => $data['resource']['id']]);
		}
		elseif (isset($data['resource']))
		{
			unset($data['resource']);
		}

		if (in_array('subresource', $objs) || in_array('all', $objs))
		{
			$data['subresource'] = $this->subresource;
			$data['subresource']['api'] = route('api.resources.subresources.read', ['id' => $data['subresource']['id']]);
		}
		elseif (isset($data['subresource']))
		{
			unset($data['subresource']);
		}

		if (in_array('schedulerpolicy', $objs) || in_array('all', $objs))
		{
			$data['schedulerpolicy'] = $this->schedulerPolicy;
			$data['schedulerpolicy']['api'] = route('api.queues.schedulerpolicies.read', ['id' => $data['schedulerpolicy']['id']]);
		}

		if (in_array('scheduler', $objs) || in_array('all', $objs))
		{
			$data['scheduler'] = $this->scheduler;
			if ($this->scheduler)
			{
				$data['scheduler']['api'] = route('api.queues.schedulers.read', ['id' => $data['scheduler']['id']]);
			}
		}
		elseif (isset($data['scheduler']))
		{
			unset($data['scheduler']);
		}

		$data['sizes'] = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.sizes.read', ['id' => $item->id]);
			});

		$data['priorsizes'] = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNotNull('datetimestop')
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
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.loans.read', ['id' => $item->id]);
			});

		$data['priorloans'] = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNotNull('datetimestop')
					->where('datetimestop', '<=', $now->toDateTimeString());
			})
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.loans.read', ['id' => $item->id]);
			});

		$data['users'] = $this->users()
			->orderBy('id', 'asc')
			->get()
			->each(function($item, $key)
			{
				$item->api = route('api.queues.users.read', ['id' => $item->id]);
			});

		$data['priorusers'] = $this->users()
			->onlyTrashed()
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
		$data['loanednodes'] = $this->loanednodes;
		$data['active']      = $this->active;

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
