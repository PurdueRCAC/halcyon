<?php

namespace App\Modules\Widgets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WidgetResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['api'] = route('api.widgets.read', ['id' => $this->id]);
		$data['menu_assignment'] = $this->menuAssignment();

		// Permissions check
		$data['can'] = array(
			'create' => false,
			'edit'   => false,
			'delete' => false,
			'manage' => false,
			'admin'  => false,
		);

		$data['params'] = $this->params->all();

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create widgets');
			$data['can']['edit']   = $user->can('edit widgets');
			$data['can']['delete'] = $user->can('delete widgets');
			$data['can']['manage'] = $user->can('manage widgets');
			$data['can']['admin']  = $user->can('admin widgets');
		}

		return $data;
	}
}
