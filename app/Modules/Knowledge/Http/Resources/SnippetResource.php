<?php

namespace App\Modules\Knowledge\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SnippetResource extends JsonResource
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

		$data['page'] = $this->page;

		$data['api'] = route('api.knowledge.snippets.read', ['id' => $this->id]);

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create knowledge');
			$data['can']['edit']   = $user->can('edit knowledge');
			$data['can']['delete'] = $user->can('delete knowledge');
			$data['can']['manage'] = $user->can('manage knowledge');
			$data['can']['admin']  = $user->can('admin knowledge');
		}

		return $data; //parent::toArray($request);
	}
}
