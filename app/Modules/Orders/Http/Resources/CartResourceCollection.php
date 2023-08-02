<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		$data = $this->resource->content()->toArray();
		$data['total'] = $this->resource->total();

		return $data;
	}
}
