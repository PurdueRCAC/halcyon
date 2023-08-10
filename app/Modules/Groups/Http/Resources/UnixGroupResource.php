<?php

namespace App\Modules\Groups\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Events\GroupReading;

/**
 * @mixin \App\Modules\Groups\Models\UnixGroup
 */
class UnixGroupResource extends JsonResource
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

		$data['api'] = route('api.unixgroups.read', ['id' => $this->id]);

		if (auth()->user() && auth()->user()->can('manage groups'))
		{
			$data['members'] = array();
			$data['priormembers'] = array();

			foreach ($this->members()->with('user')->withTrashed()->get() as $m)
			{
				$ma = $m->toArray();
				$ma['username'] = ($m->user ? $m->user->username : trans('global.unknown'));
				$ma['name'] = ($m->user ? $m->user->name : trans('global.unknown'));

				if (!$m->trashed() && ($m->user && $m->user->trashed()))
				{
					$ma['datetimeremoved'] = $m->user->dateremoved;
				}

				if (!$m->trashed())
				{
					$ma['datetimeremoved'] = null;
				}

				$ma['api'] = route('api.unixgroups.members.read', ['id' => $m->id]);

				unset($ma['user']['api_token']);

				if ($m->trashed())
				{
					$data['priormembers'][] = $ma;
				}
				else
				{
					$data['members'][] = $ma;
				}
			}
			//$data['members'] = $this->members;
		}

		if (!$this->trashed())
		{
			$data['datetimeremoved'] = null;
		}

		$data['can']['create'] = false;
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		$data['can']['manage'] = false;
		$data['can']['admin']  = false;

		$user = auth()->user();

		if ($user)
		{
			$managerids = $this->group->managers->pluck('userid')->toArray();

			$data['can']['create'] = ($user->can('manage groups') || in_array($user->id, $managerids));
			$data['can']['edit']   = ($user->can('edit groups') || ($user->can('edit.own groups') && in_array($user->id, $managerids)));
			$data['can']['delete'] = ($user->can('delete groups') || in_array($user->id, $managerids));
			$data['can']['manage'] = $user->can('manage groups');
			$data['can']['admin']  = $user->can('admin groups');
		}

		// [!] Legacy compatibility
		if (request()->segment(1) == 'ws')
		{
			$data['id'] = '/ws/unixgroup/' . $data['id'];

			$data['created'] = $this->datetimecreated->toDateTimeString();
			$data['removed'] = $this->trashed() ? $this->datetimeremoved->toDateTimeString() : '0000-00-00 00:00:00';

			unset($data['datetimecreated']);
			unset($data['datetimeremoved']);

			$data['unixgroupusers'] = array();

			foreach ($this->members as $user)
			{
				$data['unixgroupusers'][] = array(
					'id' => '/ws/unixgroupmember/' . $user->id,
					'unixgroupid' => $user->unixgroupid,
					'userid' => $user->userid,
					'username' => ($user->user ? $user->user->username : '')
				);
			}
		}

		return $data;
	}
}
