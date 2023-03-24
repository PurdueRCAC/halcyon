<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubresourceResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   Request  $request
	 * @return  array
	 */
	public function toArray(Request $request)
	{
		$this->queues->each(function($item, $key)
		{
			$item->api = route('api.queues.read', ['id' => $this->id]);
		});

		$data = parent::toArray($request);

		$data['totalcores']  = $this->totalcores;
		$data['totalnodes']  = $this->totalnodes;
		$data['soldcores']   = $this->soldcores;
		$data['soldnodes']   = $this->soldnodes;
		$data['loanedcores'] = $this->loanedcores;
		$data['loanednodes'] = $this->loanednodes;
		$data['queuestatus'] = $this->queuestatus;

		$data['api'] = route('api.resources.subresources.read', ['id' => $this->id]);

		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			$data['id'] = '/ws/subresource/' . $data['id'];
			$data['resource'] = '/ws/resource/' . $data['resourceid'];

			if (!$this->trashed())
			{
				$data['datetimeremoved'] = '0000-00-00 00:00:00';
			}
		}

		return $data;
	}
}