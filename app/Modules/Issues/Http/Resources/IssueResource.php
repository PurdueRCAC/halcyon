<?php

namespace App\Modules\Issues\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * @mixin \App\Modules\Issues\Models\Issue
 */
class IssueResource extends JsonResource
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

		foreach ($this->comments as $comment)
		{
			$data['comments'][] = new CommentResource($comment);
		}
		$data['username'] = $this->creator ? $this->creator->name : trans('global.unknown');
		$data['resources'] = $this->resources->each(function ($res, $key)
		{
			$res->api = route('api.resources.read', ['id' => $res->id]);
			$res->name = $res->resource ? $res->resource->name : $res->resourceid;
		});
		$data['age'] = Carbon::now()->timestamp - $this->datetimecreated->timestamp;

		$data['api'] = route('api.issues.read', ['id' => $this->id]);
		$data['url'] = route('site.issues.show', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit issues') || ($user->can('edit.own issues') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete issues');
		}

		return $data;
	}
}
