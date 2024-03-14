<?php

namespace App\Modules\Menus\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Menus\Models\Type
 */
class MenuResource extends JsonResource
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

		$data['api'] = route('api.menus.read', ['id' => $this->resource->id]);
		$data['items_count'] = $this->resource->items()->count();
		$data['counts'] = [
			'published' => number_format($this->resource->countPublishedItems()),
			'unpublished' => number_format($this->resource->countUnpublishedItems()),
			'trashed' => number_format($this->resource->countTrashedItems()),
		];

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
