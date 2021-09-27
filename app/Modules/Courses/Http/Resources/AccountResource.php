<?php

namespace App\Modules\Courses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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

		$data['api'] = route('api.courses.read', ['id' => $this->id]);

		if (auth()->user() && auth()->user()->can('manage courses'))
		{
			$members = $this->members()
				->get();

			$data['members'] = array();
			foreach ($members as $item)
			{
				$data['members'][] = new MemberResource($item);
			}
		}

		if (!$this->hasStart())
		{
			$data['datetimestart'] = null;
		}

		if (!$this->hasEnd())
		{
			$data['datetimestop'] = null;
		}

		if (!$this->trashed())
		{
			$data['datetimeremoved'] = null;
		}

		$data['can'] = array(
			'edit'   => false,
			'delete' => false,
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
			$data['id'] = '/ws/classaccount/' . $data['id'];
			$data['user'] = '/ws/user/' . $data['userid'];
			$data['resource'] = '/ws/resource/' . $data['resourceid'];

			if (!$data['datetimecreated'])
			{
				$data['datetimecreated'] = '0000-00-00 00:00:00';
			}
			else
			{
				$data['datetimecreated'] = $this->datetimecreated->format('Y-m-d h:i:s');
			}

			if (!$this->hasStart())
			{
				$data['datetimestart'] = '0000-00-00 00:00:00';
			}
			else
			{
				$data['datetimestart'] = $this->datetimestart->format('Y-m-d h:i:s');
			}

			if (!$this->hasEnd())
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
