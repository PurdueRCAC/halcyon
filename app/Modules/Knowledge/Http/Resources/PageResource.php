<?php

namespace App\Modules\Knowledge\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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

		$data['api'] = route('api.knowledge.read', ['id' => $this->id]);
		if (!$this->path)
		{
			$data['url'] = route('site.knowledge.index');
		}
		else
		{
			$data['url'] = route('site.knowledge.page', ['uri' => $this->path]);
		}

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

		return $data;
	}
}