<?php
namespace App\Modules\Queues\LogProcessors;

use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\History\Models\Log;

/**
 * Queue membership log processor
 */
class QueueMemberships
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->app == 'api' && substr($record->uri, 0, strlen('/api/queues/users')) != '/api/queues/users')
		{
			return $record;
		}

		if ($record->classname == 'UsersController'
		 || $record->classname == 'queuemember'
		 || $record->classname == 'groupqueuemember')
		{
			if ($record->transportmethod == 'DELETE')
			{
				$segments = strstr($record->uri, '?') ? strstr($record->uri, '?', true) : $record->uri;
				$segments = explode('/', $segments);
				$id = array_pop($segments);

				if ($record->classname == 'UsersController')
				{
					$queueuser = QueueUser::query()
						->withTrashed()
						->where('id', '=', $id)
						->first();

					if ($queueuser)
					{
						$record->targetuserid = $queueuser->userid;
						$record->targetobjectid = $queueuser->queueid;
						$record->save();
					}
				}
			}

			if ($queueid = $record->getExtraProperty('queueid'))
			{
				$queue = Queue::find($queueid);
			}
			else
			{
				$queue = Queue::find($record->targetobjectid);
			}

			$obj = trans('global.unknown');
			if ($queue)
			{
				$route = route('site.users.account.section.show.subsection', [
					'section' => 'groups',
					'id' => $queue->groupid,
					'subsection' => 'queues',
					//'u' => $record->targetuserid
				]);

				$obj = '<a href="' . $route . '">' . $queue->name . ' (' . $queue->subresource->name . ')</a>';
			}

			if ($record->classmethod == 'create')
			{
				$record->summary = 'Added to queue ' . $obj;
			}

			if ($record->classmethod == 'delete')
			{
				$record->summary = 'Removed from queue ' . $obj;
			}

			if ($record->user)
			{
				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary .= ' by <a href="' . route('site.users.account', ['u' => $record->user->id]) . '">' . $record->user->name . '</a>';
				}
				else
				{
					$record->summary .= ' by ' . $record->user->name;
				}
			}
		}

		return $record;
	}
}
