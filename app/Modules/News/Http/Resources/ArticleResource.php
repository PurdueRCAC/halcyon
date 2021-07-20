<?php

namespace App\Modules\News\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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

		$data['api'] = route('api.news.read', ['id' => $this->id]);

		$data['uri'] = route('site.news.show', ['id' => $this->id]);
		$data['formatteddate'] = $this->formatDate($this->getOriginal('datetimenews'), $this->getOriginal('datetimenewsend'));
		$data['formattedbody'] = $this->formattedBody;
		$data['formattededitdate']    = $this->formatDate($this->getOriginal('datetimeedited'));
		$data['formattedcreateddate'] = $this->formatDate($this->getOriginal('datetimecreated'));
		$data['formattedupdatedate']  = $this->formatDate($this->getOriginal('datetimeupdate'));

		$data['updates'] = array();
		foreach ($this->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
		{
			$data['updates'][] = new UpdateResource($update);
		}

		/*$data['resources'] = $this->resources->each(function ($res, $key)
		{
			$res->name = $res->resource->name;
		});*/
		$data['resources'] = $this->resourceList()->get();

		$data['associations'] = $this->associations->each(function ($res, $key)
		{
			$res->name = trans('global.unknown');
			if ($associated = $res->associated)
			{
				$res->name = $associated->name;
			}
		});

		//$this->username = $this->creator ? $this->creator->name : trans('global.unknown');
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
