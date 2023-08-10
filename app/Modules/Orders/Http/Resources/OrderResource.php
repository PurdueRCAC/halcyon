<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Orders\Models\Order
 */
class OrderResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['formattedtotal'] = $this->ordertotal ? $this->formatNumber($this->ordertotal) : $this->formattedTotal;
		$data['accounts'] = $this->accounts;
		$data['items'] = $this->items;

		$data['api'] = route('api.orders.read', ['id' => $this->id]);
		$data['url'] = route('site.orders.read', ['id' => $this->id]);

		$data['can'] = array();
		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create orders');
			$data['can']['edit']   = ($user->can('edit orders') || ($user->can('edit.own orders') && ($this->userid == $user->id || $this->submitteruserid == $user->id)));
			$data['can']['delete'] = $user->can('delete orders');
			$data['can']['manage'] = $user->can('manage orders');
			$data['can']['admin']  = $user->can('admin orders');
		}

		return $data;
	}
}
