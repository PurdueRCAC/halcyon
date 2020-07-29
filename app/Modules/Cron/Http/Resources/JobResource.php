<?php

namespace App\Modules\Cron\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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

		$data['api'] = route('api.cron.read', ['id' => $this->id]);
		$data['next_run'] = $this->nextRun();

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit cron') || ($user->can('edit.own cron') && $this->created_by == $user->id));
			$data['can']['delete'] = $user->can('delete cron');
		}

		return $data;
	}
}
