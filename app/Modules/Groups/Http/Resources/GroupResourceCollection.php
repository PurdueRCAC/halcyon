<?php

namespace App\Modules\Groups\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$request->merge(['minimal' => 1]);

		return parent::toArray($request);
	}
}
