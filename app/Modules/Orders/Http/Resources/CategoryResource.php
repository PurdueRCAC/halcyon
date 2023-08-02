<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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

		$data['products_count'] = $this->products()->count();

		$data['api'] = route('api.orders.categories.read', ['id' => $this->id]);
		$data['url'] = route('site.orders.categories.edit', ['id' => $this->id]);

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create orders.categories');
			$data['can']['edit']   = $user->can('edit orders.categories');
			$data['can']['delete'] = $user->can('delete orders.categories');
			$data['can']['manage'] = $user->can('manage orders');
			$data['can']['admin']  = $user->can('admin orders');
		}

		return $data; //parent::toArray($request);
	}
}
