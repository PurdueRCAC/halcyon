<?php

namespace App\Modules\ContactReports\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ReportResource extends JsonResource
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

		$user = auth()->user();

		$data['formatteddate'] = $this->formatDate($this->datetimecreated->toDateTimeString());
		$data['formattedreport'] = $this->formattedReport;
		$data['comments'] = array();

		$data['subscribed'] = 0;
		$data['subscribedcommentid'] = 0;
		foreach ($this->comments as $comment)
		{
			if ($user && $comment->userid == $user->id)
			{
				$data['subscribed'] = $comment->comment ? 1 : 2;

				if (!$comment->comment)
				{
					$data['subscribedcommentid'] = $comment->id;
					//continue;
				}
			}

			$data['comments'][] = new CommentResource($comment);
		}
		$data['type'] = $this->type;
		//$data['type']->api = route('api.contactreports.types.read', ['id' => $this->contactreporttypeid]);
		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');
		$data['users'] = $this->users->each(function ($res, $key)
		{
			if ($res->user)
			{
				$res->user->api = route('api.users.read', ['id' => $res->id]);
			}
			$res->name = $res->user ? $res->user->name : trans('global.unknown');
		});
		$data['groupname'] = $this->group ? $this->group->name : null;
		$data['resources'] = $this->resources->each(function ($res, $key)
		{
			if ($res->resource)
			{
				$res->resource->api = route('api.resources.read', ['id' => $res->resourceid]);
				$res->name = $res->resource->name;
			}
		});
		$data['tags'] = $this->tags;
		$data['age'] = Carbon::now()->timestamp - $this->datetimecreated->timestamp;

		$data['api'] = route('api.contactreports.read', ['id' => $this->id]);
		$data['url'] = route('site.contactreports.show', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if ($user)
		{
			if (!$data['subscribed'] && $data['userid'] == $user->id)
			{
				$data['subscribed'] = 1;
			}

			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
		}

		return $data;
	}
}
