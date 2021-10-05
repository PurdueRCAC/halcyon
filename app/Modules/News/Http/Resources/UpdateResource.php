<?php

namespace App\Modules\News\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UpdateResource extends JsonResource
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

		$data['api'] = route('api.news.updates.read', ['news_id' => $this->newsid, 'id' => $this->id]);

		$data['formattedbody'] = $this->formattedBody;
		$data['formattededitdate'] = $this->datetimeedited ? $this->formatDate($this->datetimeedited->toDateTimeString()) : null;
		$data['formattedcreateddate'] = $this->datetimecreated ? $this->formatDate($this->datetimecreated->toDateTimeString()) : null;

		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();
		if (!$user)
		{
			if (auth()->guard('api')->check())
			{
				$user = auth()->guard('api')->user();
			}
		}

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit news') || ($user->can('edit.own news') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete news');
		}

		return $data;
	}
}
