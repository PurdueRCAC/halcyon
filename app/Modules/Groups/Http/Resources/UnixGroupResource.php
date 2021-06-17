<?php

namespace App\Modules\Groups\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Events\GroupReading;

class UnixGroupResource extends JsonResource
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

		$data['api'] = route('api.unixgroups.read', ['id' => $this->id]);

		if (auth()->user() && auth()->user()->can('manage groups'))
		{
			$data['members'] = array();
			$data['priormembers'] = array();

			foreach ($this->members()->withTrashed()->get() as $m)
			{
				$ma = $m->toArray();
				$ma['username'] = ($m->user ? $m->user->username : trans('global.unknown'));
				$ma['name'] = ($m->user ? $m->user->name : trans('global.unknown'));

				if (!$m->isTrashed() && ($m->user && $m->user->isTrashed()))
				{
					$ma['datetimeremoved'] = $m->user->dateremoved;
				}

				if (!$m->isTrashed())
				{
					$ma['datetimeremoved'] = null;
				}

				if ($m->isTrashed())
				{
					$data['priormembers'][] = $m;
				}
				else
				{
					$data['members'][] = $ma;
				}
			}
			//$data['members'] = $this->members;
		}

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit groups') || ($user->can('edit.own groups') && $this->group->owneruserid == $user->id));
			$data['can']['delete'] = $user->can('delete groups');
		}

		return $data;
	}
}
