<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Users\Models\UserUsername;
use Carbon\Carbon;

class AllocationResourceCollection extends ResourceCollection
{
	/**
	 * Transform the queue collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		/*$now = Carbon::now();

		$this->collection->each(function($item, $key) use ($now)
		{
			$item->totalcores;

			$item->draindown = 0;
			if ($item->scheduler->hasDraindownTime())
			{
				$item->draindown = 1;
			}
			$item->defaultnodeaccesspolicy = $item->scheduler->policy->code;
			$item->nodeaccesspolicy = $item->schedulerPolicy->code;

			$item->draindown_timeremaining = 0;
			$timeremaining = $item->scheduler->datetimedraindown->timestamp - $now->timestamp;
			if ($timeremaining > 0)
			{
				$item->draindown_timeremaining = $timeremaining;
			}

			$item->setwalltime = $item->walltime;

			$members = $item->activeUsers;

			$userids = $members->pluck('userid')->toArray();

			if ($item->group)
			{
				$managers = $item->group->managers;
				foreach ($managers as $manager)
				{
					if ($manager->datecreated > $now->toDateTimeString())
					{
						continue;
					}

					if (in_array($manager->userid, $userids))
					{
						continue;
					}

					$members->push($manager);
				}
			}

			$item->usernames = $members->map(function($member)
				{
					$member->username = $member->user->username;
					return $member;
				})
				->pluck('username')
				->sort()
				->toArray();
		});*/

		return parent::toArray($request);
	}
}