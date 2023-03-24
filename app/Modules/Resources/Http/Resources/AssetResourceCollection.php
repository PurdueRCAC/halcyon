<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AssetResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   Request  $request
	 * @return  array
	 */
	public function toArray(Request $request)
	{
		$this->collection->each(function($item, $key)
		{
			$item->extended = false;
		});

		return parent::toArray($request);
	}
}