<?php

namespace App\Modules\Storage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsageResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['api'] = route('api.storage.usage.read', ['id' => $this->id]);

		// [!] Legacy compatibility
		if (request()->segment(1) == 'ws')
		{
			$data['id'] = '/ws/storagedirusage/' . $this->id;
			$data['recorded'] = $this->datetimerecorded->toDateTimeString();
		}

		return $data;
	}
}