<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MemberResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$this->collection->each(function($item, $key)
		{
			$item->makeHidden(['api_token', 'datecreated', 'dateremoved']);
			$item->facets;
		});

		return parent::toArray($request);
	}
}