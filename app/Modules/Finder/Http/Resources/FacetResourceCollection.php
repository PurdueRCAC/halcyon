<?php

namespace App\Modules\Finder\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FacetResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		return parent::toArray($request);
	}
}
