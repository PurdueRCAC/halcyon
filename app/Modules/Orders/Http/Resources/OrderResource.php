<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['accounts'] = $this->accounts;
		$data['items'] = $this->items;

		$data['api'] = route('api.orders.read', ['id' => $this->id]);
		$data['url'] = route('site.orders.show', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit orders') || ($user->can('edit.own orders') && $item->userid == $user->id));
			$data['can']['delete'] = $user->can('delete orders');
		}

		return $data; //parent::toArray($request);
	}
}