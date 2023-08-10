<?php

namespace App\Modules\ContactReports\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * @mixin \App\Modules\ContactReports\Models\Report
 */
class ReportResource extends JsonResource
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

		$user = auth()->user();

		$data['formatteddate'] = $this->formatDate($this->datetimecreated->toDateTimeString());
		$data['formattedreport'] = $this->toHtml();
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

		$highlight = config('module.contactreports.highlight', []);

		$users = array();
		$staff = array();
		$staff[] = array(
			'userid' => $this->userid,
			'contactreportid' => $this->id,
			'name' => $this->creator->name,
			'datetimelastnotify' => null,
			'highlight' => true,
		);
		foreach ($this->users as $res)
		{
			if ($res->userid == $this->userid)
			{
				continue;
			}

			$item = $res->toArray();

			$item['name'] = trans('global.unknown');
			$item['highlight'] = false;
			if ($res->user)
			{
				$item['name'] = $res->user->name;

				foreach ($res->user->getAuthorisedRoles() as $role)
				{
					if (in_array($role, $highlight))
					{
						$item['highlight'] = true;
						break;
					}
				}
			}

			if ($item['highlight'])
			{
				$staff[] = $item;
			}
			else
			{
				$users[] = $item;
			}
		};

		$data['users'] = array_merge($staff, $users);

		$data['groupname'] = $this->group ? $this->group->name : null;
		/*$data['resources'] = $this->resources->each(function ($res, $key)
		{
			if ($res->resource)
			{
				$res->resource->api = route('api.resources.read', ['id' => $res->resourceid]);
				$res->name = $res->resource->name;
			}
		});*/
		$data['resources'] = array();
		foreach ($this->resources as $res)
		{
			$item = $res->toArray();

			$item['name'] = trans('global.unknown');
			if ($res->resource)
			{
				$item['name'] = $res->resource->name;
			}

			$data['resources'][] = $item;
		};

		$data['tags'] = $this->tags;
		$data['age'] = Carbon::now()->timestamp - $this->datetimecreated->timestamp;

		$data['api'] = route('api.contactreports.read', ['id' => $this->id]);
		$data['url'] = route('site.contactreports.show', ['id' => $this->id]);

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		if ($user)
		{
			if (!$data['subscribed'] && $data['userid'] == $user->id)
			{
				$data['subscribed'] = 1;
			}

			$data['can']['create'] = $user->can('create contactreports');
			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
			$data['can']['manage'] = $user->can('manage contactreports');
			$data['can']['admin']  = $user->can('admin contactreports');
		}

		return $data;
	}
}
