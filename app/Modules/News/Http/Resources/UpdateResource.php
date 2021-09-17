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
		$data['formattededitdate']    = $this->formatDate($this->datetimeedited->toDateTimeString());
		$data['formattedcreateddate'] = $this->formatDate($this->datetimecreated->toDateTimeString());

		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}
		if (!$this->isModified())
		{
			$data['datetimeedited'] = null;
		}

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
