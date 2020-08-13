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
		//$item->canCreate = false;
		$data['can'] = array(
			'edit' => false,
			'delete' => false,
		);

		$data['params'] = $this->params->all();

		if (auth()->user())
		{
			//$item->can['create'] = auth()->user()->can('create widgets');
			$data['can']['edit']   = auth()->user()->can('edit widgets');
			$data['can']['delete'] = auth()->user()->can('delete widgets');
		}

		return $data;
	}
}
