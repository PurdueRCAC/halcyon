<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$user = auth()->user();

		$this->collection->each(function ($item, $key) use ($user)
		{
			$item->setAttribute('api', route('api.orders.products.read', ['id' => $item->id]));
			$item->setAttribute('url', route('site.orders.products.read', ['id' => $item->ordercategoryid]));

			// Permissions check
			$can = array(
				'edit'   => false,
				'delete' => false,
			);

			if ($user)
			{
				$can['edit']   = ($user->can('edit orders.products') || ($user->can('edit.own orders.products') && $item->userid == $user->id));
				$can['delete'] = $user->can('delete orders.products');
			}

			$item->setAttribute('can', $can);
		});

		return parent::toArray($request);
	}
}