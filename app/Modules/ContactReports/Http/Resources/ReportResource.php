<?php

namespace App\Modules\ContactReports\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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

		$data['formatteddate'] = $this->formatDate($this->datetimecreated->toDateTimeString());
		$data['formattedreport'] = $this->formattedReport;
		$data['comments'] = $this->comments;
		$data['users'] = $this->users;
		$data['resources'] = $this->resources;

		$data['api'] = route('api.contactreports.read', ['id' => $this->id]);
		$data['url'] = route('site.contactreports.show', ['id' => $this->id]);

		//$data['canCreate'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			//$data['canCreate'] = auth()->user()->can('create contactreports');
			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $item->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
		}

		return $data; //parent::toArray($request);
	}
}