<?php
namespace App\Listeners\Users\Queues;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserDisplay;

/**
 * User listener for queues
 */
class Queues
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

	/**
	 * Display queue membership data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event): void
	{
		if (!auth()->user())
		{
			return;
		}

		$user = $event->getUser();

		// Owner groups
		$memberships = $user->groups()
			->where('groupid', '>', 0)
			->whereIsManager()
			->get();

		$qu = (new \App\Modules\Queues\Models\User)->getTable();
		$q = (new \App\Modules\Queues\Models\Queue)->getTable();
		$s = (new \App\Modules\Queues\Models\Scheduler)->getTable();
		$r = (new \App\Modules\Resources\Models\Subresource)->getTable();
		$c = (new \App\Modules\Resources\Models\Child)->getTable();
		$a = (new \App\Modules\Resources\Models\Asset)->getTable();

		$ids = array();
		$allqueues = array();
		foreach ($memberships as $membership)
		{
			$group = $membership->group;

			if (!$group)
			{
				continue;
			}

			$queues = $group->queues;

			$queues = $group->queues()
				->select($q . '.*')
				->join($s, $s . '.id', $q . '.schedulerid')
				->join($r, $r . '.id', $q . '.subresourceid')
				->join($c, $c . '.subresourceid', $r . '.id')
				->join($a, $a . '.id', $c . '.resourceid')
				->whereNull($s . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->whereNull($a . '.datetimeremoved')
				->orderBy($r . '.name', 'asc')
				->orderBy($q . '.name', 'asc')
				->get();

			foreach ($queues as $queue)
			{
				$ids[] = $queue->id;

				$queue->status = 'member';

				$allqueues[] = $queue;
			}
		}

		$queues = $user->queues()
			->select($qu . '.*')
			->join($q, $q . '.id', $qu . '.queueid')
			->join($s, $s . '.id', $q . '.schedulerid')
			->join($r, $r . '.id', $q . '.subresourceid')
			->join($c, $c . '.subresourceid', $r . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($q . '.datetimeremoved')
			->whereNull($s . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->whereNull($a . '.datetimeremoved')
			->whereNotIn($qu . '.queueid', $ids)
			->orderBy($r . '.name', 'asc')
			->orderBy($q . '.name', 'asc')
			->get();

		foreach ($queues as $qu)
		{
			if ($qu->trashed())
			{
				continue;
			}

			$queue = $qu->queue;

			$group = $queue->group;

			if (!$group || !$group->id)
			{
				continue;
			}

			if ($qu->isPending())
			{
				$queue->status = 'pending';
			}
			else
			{
				$queue->status = 'member';
			}

			$allqueues[] = $queue;
		}

		/*app('translator')->addNamespace(
			'listener.users.queues',
			__DIR__ . '/lang'
		);*/

		$content = view('queues::site.profile', [
			'user' => $user,
			'queues' => $allqueues,
		]);

		$event->addPart(
			$content
		);
	}
}
