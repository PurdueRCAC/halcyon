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

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit knowledge') || ($user->can('edit.own knowledge') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete knowledge');
		}

		return $data;
	}
}