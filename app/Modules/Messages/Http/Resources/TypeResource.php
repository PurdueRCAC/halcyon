<?php

namespace App\Modules\Messages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Messages\Models\Type
 */
class TypeResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			$data['id'] = '/ws/messagequeuetype/' . $data['id'];
		}

		$data['api'] = route('api.messages.types.read', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if (auth()->user())
		{
			$data['can']['edit']   = auth()->user()->can('edit messages.types');
			$data['can']['delete'] = auth()->user()->can('delete messages.types');
		}

		return $data;
	}
}