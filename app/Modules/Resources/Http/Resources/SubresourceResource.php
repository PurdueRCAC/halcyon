<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubresourceResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$this->queues;

		$data = parent::toArray($request);

		$data['totalcores']  = $this->totalcores;
		$data['totalnodes']  = $this->totalnodes;
		$data['soldcores']   = $this->soldcores;
		$data['soldnodes']   = $this->soldnodes;
		$data['loanedcores'] = $this->loanedcores;
		$data['loanednodes'] = $this->loanednodes;
		$data['queuestatus'] = $this->queuestatus;

		$data['api'] = route('api.resources.subresources.read', ['id' => $this->id]);

		return $data;
	}
}