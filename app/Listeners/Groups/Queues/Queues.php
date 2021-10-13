<?php
namespace App\Listeners\Groups\Queues;

use App\Modules\Groups\Events\GroupDisplay;
use App\Modules\Groups\Events\GroupReading;
use App\Modules\Queues\Models\Queue;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;

/**
 * Queues listener for group events
 */
class Queues
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(GroupDisplay::class, self::class . '@handleGroupDisplay');
		$events->listen(GroupReading::class, self::class . '@handleGroupReading');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleGroupReading(GroupReading $event)
	{
		$q = (new Queue)->getTable();
		$s = (new Subresource)->getTable();
		$r = (new Asset)->getTable();
		$c = (new Child)->getTable();

		$queues = Queue::query()
			->join($s, $s . '.id', $q . '.subresourceid')
			->join($c, $c . '.subresourceid', $q . '.subresourceid')
			->join($r, $r . '.id', $c . '.resourceid')
			->whereNull($q . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->where('groupid', '=', $event->group->id)
			->get();

		$resources = array();
		foreach ($queues as $queue)
		{
			$resources[] = $queue->resource;
		}

		$event->group->queues = $queues;
		$event->group->resources = $resources;
	}

	/**
	 * Load Queues data when displaying a Group
	 *
	 * @param   GroupDisplay  $event
	 * @return  void
	 */
	public function handleGroupDisplay(GroupDisplay $event)
	{
		$content = null;
		$group = $event->getGroup();
		$client = app('isAdmin') ? 'admin' : 'site';

		if ($event->getActive() == 'queues' || $client == 'admin')
		{
			$content = view('queues::site.group', [
				'group' => $group
			]);
		}

		$event->addSection(
			'queues',
			trans('queues::queues.queues'),
			($event->getActive() == 'queues'),
			$content
		);
	}
}
