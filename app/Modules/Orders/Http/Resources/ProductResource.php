<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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

		$data['api'] = route('api.orders.products.read', ['id' => $this->id]);
		$data['url'] = route('site.orders.products.read', ['id' => $this->ordercategoryid]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit orders.products') || ($user->can('edit.own orders.products') && $item->userid == $user->id));
			$data['can']['delete'] = $user->can('delete orders.products');
		}

		return $data; //parent::toArray($request);
	}
}
