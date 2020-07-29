<?php

namespace App\Modules\Pages\Http\Resources;

use App\Modules\Pages\Events\PageContentIsRendering;
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
		$this->setAttribute('api', route('api.pages.read', ['id' => $this->id]));
		$this->setAttribute('url', url('/') . '/' . $this->path);

		event($event = new PageContentIsRendering($this->content));
		$this->setAttribute('content', $event->getBody());

		// Permissions check
		$can = [
			'edit'   => false,
			'delete' => false
		];

		if (auth()->user())
		{
			$can['edit']   = (auth()->user()->can('edit pages') || (auth()->user()->can('edit.own pages') && $this->created_by == auth()->user()->id));
			$can['delete'] = auth()->user()->can('delete pages');
		}

		$this->setAttribute('can', $can);

		return parent::toArray($request);
	}
}
