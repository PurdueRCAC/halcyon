<?php

namespace App\Modules\Tags\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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

		$data['api'] = route('api.tags.read', ['id' => $this->id]);

		// Permissions check
		$data['can'] = array(
			'create' => false,
			'edit'   => false,
			'delete' => false,
			'manage' => false,
			'admin'  => false,
		);

		$user = auth()->user();
		if (!$user)
		{
			if (auth()->guard('api')->check())
			{
				$user = auth()->guard('api')->user();
			}
		}

		if ($user)
		{
			$data['can']['create'] = $user->can('create tags');
			$data['can']['edit'] = ($user->can('edit tags') || ($user->can('edit.own tags') && $this->created_by == $user->id));
			$data['can']['delete'] = $user->can('delete tags');
			$data['can']['manage'] = $user->can('manage tags');
			$data['can']['admin'] = $user->can('admin tags');
		}

		return $data;
	}
}
