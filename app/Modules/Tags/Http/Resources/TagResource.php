<?php

namespace App\Modules\Tags\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagsResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$this->setAttribute('api', route('api.tags.read', ['id' => $this->id]));

		$data = parent::toArray($request);

		// Permissions check
		$data['can'] = array(
			'edit' => false,
			'delete' => false,
		);

		if (auth()->user())
		{
			$data['can']['edit'] = (auth()->user()->can('edit tags') || (auth()->user()->can('edit.own tags') && $this->created_by == auth()->user()->id));
			$data['can']['delete'] = auth()->user()->can('delete tags');
		}

		return $data;
	}
}
