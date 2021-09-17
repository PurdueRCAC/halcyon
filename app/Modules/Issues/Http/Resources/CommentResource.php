<?php

namespace App\Modules\Issues\Http\Resources;

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

		$data['formatteddate'] = $this->formattedDate;
		$data['formattedcomment'] = $this->formattedComment;
		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');

		$data['api'] = route('api.issues.comments.read', ['comment' => $this->id]);

		unset($data['report']);

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit issues') || ($user->can('edit.own issues') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete issues');
		}

		return $data;
	}
}
