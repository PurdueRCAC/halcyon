<?php

namespace App\Modules\Knowledge\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Knowledge\Models\Associations;

/**
 * @mixin \App\Modules\Knowledge\Models\Associations
 */
class PageResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		// Get the ancestors' variables
		$prev = null;
		foreach (Associations::stackByPath($this->path) as $assoc)
		{
			if ($assoc->id == $this->id)
			{
				$this->page->mergeVariables($prev->page->variables);
				break;
			}

			if ($prev && $prev->page)
			{
				$assoc->page->mergeVariables($prev->page->variables);
			}

			$prev = $assoc;
		}

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