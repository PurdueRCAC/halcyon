<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QueueResourceCollection extends ResourceCollection
{
	/**
	 * Transform the queue collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		return parent::toArray($request);
	}
}