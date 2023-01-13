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
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$this->setAttribute('api', route('api.pages.read', ['id' => $this->id]));
		$this->setAttribute('url', url('/') . '/' . $this->path);

		event($event = new PageContentIsRendering($this->content));
		$this->setAttribute('content', $event->getBody());

		// Permissions check
		$can = [
			'create' => false,
			'edit'   => false,
			'delete' => false,
			'manage' => false,
			'admin'  => false,
		];

		$user = auth()->user();

		if ($user)
		{
			$can['create'] = $user->can('create pages');
			$can['edit']   = ($user->can('edit pages') || ($user->can('edit.own pages') && $this->created_by == $user->id));
			$can['delete'] = $user->can('delete pages');
			$can['manage'] = $user->can('manage pages');
			$can['admin']  = $user->can('admin pages');
		}

		$this->setAttribute('can', $can);

		return parent::toArray($request);
	}
}
