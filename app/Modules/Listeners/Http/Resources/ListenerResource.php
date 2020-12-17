<?php

namespace App\Modules\Listeners\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListenerResource extends JsonResource
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

		$data['api'] = route('api.listeners.read', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit listeners') || ($user->can('edit.own listeners') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete listeners');
		}

		return $data;
	}
}