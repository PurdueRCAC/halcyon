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
				->with('user')
				->get();

			$data['members'] = array();
			foreach ($members as $item)
			{
				$data['members'][] = new MemberResource($item);
			}
		}

		$data['user'] = array(
			'id' => $this->userid,
			'name' => $this->user ? $this->user->name : trans('global.unknown'),
			'username' => $this->user ? $this->user->username : trans('global.unknown'),
		);

		$data['can'] = array(
			'create' => false,
			'edit'   => false,
			'delete' => false,
			'manage' => false,
			'admin'  => false,
		);

		$user = auth()->user();

		if ($user)
		{
			$data['can']['create'] = $user->can('create courses');
			$data['can']['edit']   = ($user->can('edit courses') || ($user->can('edit.own courses') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete courses');
			$data['can']['manage'] = $user->can('manage courses');
			$data['can']['admin']  = $user->can('admin courses');
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
