<?php

namespace App\Modules\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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

		$data['api'] = route('api.courses.members.read', ['id' => $this->id]);

		$data['can'] = array(
			'edit'   => false,
			'delete' => false,
		);

		$data['user'] = array(
			'id' => $this->userid,
			'name' => $this->user ? $this->user->name : trans('global.unknown'),
			'username' => $this->user ? $this->user->username : trans('global.unknown'),
		);

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit groups') || ($user->can('edit.own groups') && $this->owneruserid == $user->id));
			$data['can']['delete'] = $user->can('delete groups');
		}

		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			$data['id'] = '/ws/classuser/' . $this->id;
			$data['user'] = '/ws/user/' . $this->userid;
			$data['start'] = $this->datetimestart;
			$data['stop']  = $this->hasStopped() ? $this->datetimestop : '0000-00-00 00:00:00';

			if (!$data['datetimecreated'])
			{
				$data['datetimecreated'] = '0000-00-00 00:00:00';
			}
			else
			{
				$data['datetimecreated'] = $this->datetimecreated->format('Y-m-d h:i:s');
			}

			if (!$data['datetimestart'])
			{
				$data['datetimestart'] = '0000-00-00 00:00:00';
			}
			else
			{
				$data['datetimestart'] = $this->datetimestart->format('Y-m-d h:i:s');
			}

			if (!$data['datetimestop'])
			{
				$data['datetimestop'] = '0000-00-00 00:00:00';
			}
			else
			{
				$data['datetimestop'] = $this->datetimestop->format('Y-m-d h:i:s');
			}

			if (!$this->trashed())
			{
				$data['datetimeremoved'] = '0000-00-00 00:00:00';
			}
			else
			{
				$data['datetimeremoved'] = $this->datetimeremoved->format('Y-m-d h:i:s');
			}
		}

		return $data;
	}
}
