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
				->withTrashed()
				->whereIsActive()
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

		if (!$this->isTrashed())
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
		}

		return $data;
	}
}
