<?php

namespace App\Modules\Queues\LogProcessors;

use App\Modules\Queues\Models\Queue;
use App\Modules\History\Models\Log;
use App\Modules\Groups\Models\Group;

/**
 * User requests log processor
 */
class UserRequests
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'UserRequestsController'
		 || $record->classname == 'userrequest')
		{
			$queuenames = array();
			$queuename = '#' . $record->targetobjectid;

			if ($resources = $record->getExtraProperty('resources', []))
			{
				$group = Group::find($record->groupid);

				foreach ($resources as $resourceid)
				{
					foreach ($group->queues as $queue)
					{
						if ($queue->resource && $queue->resource->id == $resourceid)
						{
							$queuenames[] = $queue->name . ' (' . ($queue->subresource ? $queue->subresource->name : trans('global.unknown')) . ')';
						}
					}
				}
			}
			else
			{
				$queue = Queue::find($record->targetobjectid);
				if ($queue)
				{
					$queuenames[] = $queue->name . ' (' . ($queue->subresource ? $queue->subresource->name : trans('global.unknown')) . ')';
				}
			}

			$queuename = implode(', ', $queuenames);

			if ($record->classmethod == 'create')
			{
				$record->summary = 'Submitted request to queue ' . $queuename;
			}

			if ($record->classmethod == 'update')
			{
				$record->summary = 'Approved request to queue ' . $queuename;
			}

			if ($record->classmethod == 'delete')
			{
				$record->summary = 'Canceled request to queue ' . $queuename;
			}
		}

		return $record;
	}
}
