<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Orders\Entities\Cart
 */
class CartResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		//$data = parent::toArray($request);
		//$data['api'] = route('api.orders.cart.read', ['id' => $this->rowId]);

		$data = array();
		$data['data'] = array_values($this->resource->content()->sortBy('name')->toArray());
		$data['tax'] = $this->resource->tax();
		$data['subtotal'] = $this->resource->subtotal();
		$data['total'] = $this->resource->total();

		return $data;
	}
}
