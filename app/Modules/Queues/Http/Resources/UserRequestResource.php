<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as Member;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\UserRequest;

class UserRequestResource extends JsonResource
{
	/**
	 * Transform the queue collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$u = (new Member)->getTable();
		$q = (new Queue)->getTable();

		$numrequests = Member::query()
			->select($u . '.*', $q . '.groupid')
			->join($q, $q . '.id', $u . '.queueid')
			->where($u . '.userrequestid', '=', $this->id)
			->wherePendingRequest()
			->get();

		if (count($numrequests) == 0)
		{
			$g = (new GroupUser)->getTable();

			$numrequests = Member::query()
				->select($u . '.*', $g . '.groupid')
				->join($g, $g . '.queueuserid', $u . '.id')
				->join($q, $q . '.id', $u . '.queueid')
				->where($g . '.userrequestid', '=', $this->id)
				->wherePendingRequest()
				->get();
		}

		$queues = array();
		$resources = array();

		foreach ($numrequests as $numrequest)
		{
			// See if user is already a member of this group
			$isMember = Member::query()
				->select($u . '.*', $q . '.groupid')
				->join($q, $q . '.id', $u . '.queueid')
				->where($u . '.userid', '=', $numrequest->userid)
				->where($q . '.groupid', '=', $numrequest->groupid)
				->whereIsMember()
				->count();

			if ($isMember)
			{
				$queues[] = $numrequest;
			}
			else
			{
				// Only show resources
				$resources[] = $numrequest->resource;
			}
		}

		$data['resources'] = $resources;
		$data['queues'] = $queues;

		$data['api'] = route('api.queues.requests.read', ['id' => $this->id]);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit queues.requests') || ($user->can('edit.own queues.requests') && $this->userid == $user->id));
			$data['can']['delete'] = $user->can('delete queues.requests');
		}

		return $data;
	}
}
