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

		$data['resources'] = $this->resourceList()->get();

		$data['associations'] = $this->associations->each(function ($res, $key)
		{
			$res->name = trans('global.unknown');
			if ($associated = $res->associated)
			{
				$res->name = $associated->name;
			}
		});

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

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
			$data['can']['create'] = $user->can('create news');
			$data['can']['edit']   = ($user->can('edit news') || ($user->can('edit.own news') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete news');
			$data['can']['manage'] = $user->can('manage news');
			$data['can']['admin']  = $user->can('admin news');
		}

		return $data;
	}
}
