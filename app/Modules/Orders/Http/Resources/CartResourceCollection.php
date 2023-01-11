<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$data = $this->content()->toArray();
		$data['total'] = $this->total();

		return $data;
	}
}
