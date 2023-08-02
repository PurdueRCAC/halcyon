<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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

		$data['api'] = route('api.orders.products.read', ['id' => $this->id]);
		$data['url'] = route('site.orders.products.read', ['id' => $this->ordercategoryid]);

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create orders.products');
			$data['can']['edit']   = $user->can('edit orders.products');
			$data['can']['delete'] = $user->can('delete orders.products');
			$data['can']['manage'] = $user->can('manage orders');
			$data['can']['admin']  = $user->can('admin orders');
		}

		return $data; //parent::toArray($request);
	}
}
