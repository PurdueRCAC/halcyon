<?php

namespace App\Modules\News\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\News\Models\Update
 */
class UpdateResource extends JsonResource
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

		$data['api'] = route('api.news.updates.read', ['news_id' => $this->newsid, 'id' => $this->id]);

		$data['formattedbody'] = $this->toHtml();
		$data['formattededitdate'] = $this->datetimeedited ? $this->formatDate($this->datetimeedited->toDateTimeString()) : null;
		$data['formattedcreateddate'] = $this->datetimecreated ? $this->formatDate($this->datetimecreated->toDateTimeString()) : null;
		$data['vars'] = $this->getContentVars();

		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create news');
			$data['can']['edit']   = ($user->can('edit news') || ($user->can('edit.own news') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete news');
			$data['can']['manage'] = $user->can('manage news');
			$data['can']['admin']  = $user->can('admin news');
		}

		return $data;
	}
}
