<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MemberResourceCollection extends ResourceCollection
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
			$item->makeHidden(['api_token', 'datecreated', 'dateremoved']);
			foreach ($item->facets()->where('locked', '=', 1)->get() as $facet)
			{
				// If the key is already set, then we have an array of values
				if ($orig = $item->{$facet->key})
				{
					if (!is_array($orig))
					{
						$orig = array($orig);
					}

					$orig[] = $facet->value;

					$item->{$facet->key} = $orig;
				}
				else
				{
					$item->{$facet->key} = $facet->value;
				}
			}
		});

		return parent::toArray($request);
	}
}