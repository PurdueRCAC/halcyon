<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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

		$data['products_count'] = $this->products()->count();

		$data['api'] = route('api.orders.categories.read', ['id' => $this->id]);
		$data['url'] = route('site.orders.categories.edit', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = $user->can('edit orders.categories');
			$data['can']['delete'] = $user->can('delete orders.categories');
		}

		return $data; //parent::toArray($request);
	}
}
