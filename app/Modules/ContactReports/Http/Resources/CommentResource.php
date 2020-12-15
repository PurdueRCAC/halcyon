<?php

namespace App\Modules\ContactReports\Http\Resources;

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

		$data['api'] = route('api.contactreports.comments.read', ['comment' => $this->id]);

		unset($data['report']);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
		}

		return $data;
	}
}
