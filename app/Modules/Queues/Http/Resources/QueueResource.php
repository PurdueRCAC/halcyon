<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
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
		$data = parent::toArray($request);

		$now = Carbon::now();

		$data['resource'] = $this->resource()->get()->first();
		$data['subresource'] = $this->subresource;
		$data['scheduler_policy'] = $this->schedulerPolicy;
		$data['scheduler'] = $this->scheduler;
		$data['sizes'] = $this->sizes;

		$data['loans'] = $this
			->loans()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->get();

		$data['priorloans'] = $this
			->loans()
			->where(function($where) use ($now)
			{
				$where->whereNotNull('datetimestop')
					->where('datetimestop', '!=', '0000-00-00 00:00:00')
					->where('datetimestop', '<=', $now->toDateTimeString());
			})
			->get();

		$data['users'] = $this->users;
		$data['priorusers'] = $this->users()->onlyTrashed()->where('datetimeremoved', '!=', '0000-00-00 00:00:00')->get();
		$data['walltimes'] = $this->walltimes;

		$data['totalcores'] = $this->totalcores;
		$data['totalnodes'] = $this->totalnodes;
		$data['soldcores'] = $this->soldcores;
		$data['soldnodes'] = $this->soldnodes;
		$data['loanedcores'] = $this->loanedcores;
		$data['loanednodes'] = $this->loanednodes;
		$data['active'] = $this->active;

		$data['api'] = route('api.queues.read', ['id' => $this->id]);
		//$data['url'] = route('site.queues.show', ['id' => $this->id]);

		//$data['canCreate'] = false;
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
