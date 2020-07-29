<?php

namespace App\Modules\Orders\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryResourceCollection extends ResourceCollection
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
			$item->setAttribute('api', route('api.orders.categories.read', ['id' => $item->id]));
			$item->setAttribute('url', route('site.orders.categories.read', ['id' => $item->id]));

			// Permissions check
			$can = array(
				'edit'   => false,
				'delete' => false,
			);

			if ($user)
			{
				$can['edit']   = $user->can('edit orders.categories');
				$can['delete'] = $user->can('delete orders.categories');
			}

			$item->setAttribute('can', $can);
		});

		return parent::toArray($request);
	}
}