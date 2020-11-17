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
		foreach ($this->comments as $comment)
		{
			$c = $comment->toArray();

			$c['formatteddate'] = $comment->formattedDate;
			$c['formattedcomment'] = $comment->formattedComment;
			$c['username'] = $comment->creator->name;

			$c['api'] = route('api.contactreports.read', ['id' => $comment->id]);
			$c['url'] = route('site.contactreports.show', ['id' => $comment->contactreportid]);

			$c['can']['edit']   = false;
			$c['can']['delete'] = false;

			if ($user)
			{
				$c['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $comment->userid == $user->id));
				$c['can']['delete'] = $user->can('delete contactreports');
			}
			$data['comments'][] = $c;
		}
		$data['username'] = $this->creator->name;
		$data['users'] = $this->users->each(function ($res, $key)
		{
			$res->name = $res->user->name;
		});
		$data['resources'] = $this->resources->each(function ($res, $key)
		{
			$res->name = $res->resource->name;
		});
		$data['age'] = Carbon::now()->timestamp - $this->datetimecreated->timestamp;

		$data['api'] = route('api.contactreports.read', ['id' => $this->id]);
		$data['url'] = route('site.contactreports.show', ['id' => $this->id]);

		//$data['canCreate'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if ($user)
		{
			//$data['canCreate'] = auth()->user()->can('create contactreports');
			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
		}

		return $data; //parent::toArray($request);
	}
}
