<?php

namespace App\Modules\Knowledge\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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

		$data['formattedcomment'] = $this->formattedComment();

		$data['api'] = route('api.knowledge.read', ['id' => $this->id]);
		$data['url'] = route('site.knowledge.show', ['id' => $this->contactreportid]);

		unset($data['report']);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit knowledge') || ($user->can('edit.own knowledge') && $item->userid == $user->id));
			$data['can']['delete'] = $user->can('delete knowledge');
		}

		return $data; //parent::toArray($request);
	}
}
