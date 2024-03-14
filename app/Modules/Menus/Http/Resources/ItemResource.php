<?php

namespace App\Modules\Menus\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Menus\Models\Item
 */
class ItemResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['api'] = route('api.menus.items.read', ['id' => $this->resource->id]);

		// Permissions check
		$can = [
			'create' => false,
			'edit'   => false,
			'delete' => false,
			'manage' => false,
			'admin'  => false,
		];

		if ($user = auth()->user())
		{
			$can['create'] = $user->can('create menus');
			$can['edit']   = $user->can('edit menus');
			$can['delete'] = $user->can('delete menus');
			$can['manage'] = $user->can('manage menus');
			$can['admin']  = $user->can('admin menus');
		}

		$data['can'] = $can;

		return $data;
	}
}
