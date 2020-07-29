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

		$data['api'] = route('api.groups.read', ['id' => $this->id]);

		//$data['formatteddate'] = $this->formatDate($this->getOriginal('datetimenews'), $this->getOriginal('datetimenewsend'));
		$data['formattedbody'] = $this->formattedBody;
		//$data['formattededitdate']    = $this->formatDate($this->getOriginal('datetimeedited'));
		//$data['formattedcreateddate'] = $this->formatDate($this->getOriginal('datetimecreated'));
		//$data['formattedupdatedate']  = $this->formatDate($this->getOriginal('datetimeupdate'));

		//$this->username = $this->creator ? $this->creator->name : trans('global.unknown');

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit news') || ($user->can('edit.own news') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete news');
		}

		return $data;
	}
}
