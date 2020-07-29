<?php

namespace App\Modules\Messages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessagesResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$this->type;

		$data = parent::toArray($request);

		$data['api'] = route('api.messages.read', ['id' => $this->id]);
		$data['target'] = $this->target;

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if (auth()->user())
		{
			$data['can']['edit']   = auth()->user()->can('edit messages');
			$data['can']['delete'] = auth()->user()->can('delete messages');
		}

		return $data;
	}
}