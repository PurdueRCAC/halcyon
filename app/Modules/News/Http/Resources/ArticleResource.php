<?php

namespace App\Modules\News\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\News\Models\Article
 */
class ArticleResource extends JsonResource
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

		$data['api'] = route('api.news.read', ['id' => $this->id]);

		$data['uri'] = route('site.news.show', ['id' => $this->id]);

		if (!$this->template && !$this->ended() && $this->type->calendar)
		{
			$data['uri_subscribe'] = $this->subscribeCalendarLink;
			$data['uri_download'] = $this->downloadCalendarLink;
		}

		$data['formatteddate'] = $this->formatDate($this->getOriginal('datetimenews'), $this->getOriginal('datetimenewsend'));
		$data['formattedbody'] = $this->toHtml();
		$data['formattededitdate']    = $this->formatDate($this->getOriginal('datetimeedited'));
		$data['formattedcreateddate'] = $this->formatDate($this->getOriginal('datetimecreated'));
		$data['formattedupdatedate']  = $this->formatDate($this->getOriginal('datetimeupdate'));

		$data['vars'] = $this->getContentVars();

		$data['updates'] = array();
		foreach ($this->updates->sortByDesc('datetimecreated') as $update)
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
