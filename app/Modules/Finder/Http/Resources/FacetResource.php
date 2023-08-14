<?php

namespace App\Modules\Finder\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Finder\Models\Facet
 */
class FacetResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$this->resource = $event->group;

		$data = parent::toArray($request);

		$data['api'] = route('api.finder.facets.read', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = $user->can('edit finder');
			$data['can']['delete'] = $user->can('delete finder');
		}

		return $data;
	}
}
