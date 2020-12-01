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

		//$data['formatteddate'] = $this->formatDate($this->getOriginal('datetimenews'), $this->getOriginal('datetimenewsend'));
		$data['formattedbody'] = $this->formattedBody;
		//$data['formattededitdate']    = $this->formatDate($this->getOriginal('datetimeedited'));
		$data['formattedcreateddate'] = $this->formattedDatetimecreated($this->datetimecreated->toDateTimeString());
		//$data['formattedupdatedate']  = $this->formatDate($this->getOriginal('datetimeupdate'));

		//$this->username = $this->creator ? $this->creator->name : trans('global.unknown');
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
