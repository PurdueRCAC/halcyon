<?php

namespace App\Modules\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		//event($event = new AccountReading($this->resource));

		//$this->resource = $event->account;

		$data = parent::toArray($request);

		$data['api'] = route('api.courses.read', ['id' => $this->id]);

		if (auth()->user() && auth()->user()->can('manage courses'))
		{
			$data['members'] = $this->members;
		}

		$data['can'] = array(
			'edit'   => false,
			'delete' => false,
		);

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit groups') || ($user->can('edit.own groups') && $this->owneruserid == $user->id));
			$data['can']['delete'] = $user->can('delete groups');
		}

		return $data;
	}
}
