<?php

namespace App\Modules\ContactReports\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\ContactReports\Models\Comment
 */
class CommentResource extends JsonResource
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

		$data['formatteddate'] = $this->formattedDate;
		$data['formattedcomment'] = $this->formattedComment;
		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');

		$data['api'] = route('api.contactreports.comments.read', ['id' => $this->id]);

		unset($data['report']);

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create contactreports');
			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
			$data['can']['manage'] = $user->can('manage contactreports');
			$data['can']['admin']  = $user->can('admin contactreports');
		}

		return $data;
	}
}
