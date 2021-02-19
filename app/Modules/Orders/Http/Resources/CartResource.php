<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		//$data = parent::toArray($request);
		//$data['api'] = route('api.orders.cart.read', ['id' => $this->rowId]);

		$data = array();
		$data['data'] = array_values($this->content()->toArray());
		$data['tax'] = $this->tax();
		$data['subtotal'] = $this->subtotal();
		$data['total'] = $this->total();

		return $data;
	}
}
