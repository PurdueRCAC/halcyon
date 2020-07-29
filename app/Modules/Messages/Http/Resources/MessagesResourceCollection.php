<?php

namespace App\Modules\Messages\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MessagesResourceCollection extends ResourceCollection
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