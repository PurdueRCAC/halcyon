<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\Groups;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Events\GroupDisplay;

/**
 * User listener for sessions
 */
class Groups
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'groups'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		$groups = $user->groups()
			->where('groupid', '>', 0)
			->get()
			->pluck('groupid')
			->toArray();
		$groups = array_unique($groups);

		$total = count($groups);

		/*$total = $user->groups()
			//->whereIsManager()
			->where('groupid', '>', 0)
			->count();

		foreach ($user->groups as $g)
		{
			$queues = $g->group->queues()
						//->withTrashed()
						->get();
			foreach ($queues as $queue)
			{
				$total += $queue->users()->where('userid', '=', $user->id)->count();
			}
		}*/
		foreach ($user->queues()->whereIn('membertype', [1, 4])->get() as $qu)
		{
			if ($qu->isMember() && $qu->isTrashed())//$qu->trashed())
			{
				continue;
			}

			$queue = $qu->queue;

			if (!$queue || $queue->isTrashed())
			{
				continue;
			}

			if (!$queue->scheduler
			 || ($queue->scheduler->datetimeremoved
			 && $queue->scheduler->datetimeremoved != '0000-00-00 00:00:00'
			 && $queue->scheduler->datetimeremoved != '-0001-11-30 00:00:00'))
			{
				continue;
			}

			if (!in_array($queue->groupid, $groups))
			{
				$total++;
			}
		}

		if ($event->getActive() == 'groups')
		{
			app('pathway')
				->append(
					trans('groups::groups.my groups'),
					route('site.users.account.section', $r)
				);

			if ($id = request()->segment(3))
			{
				$id = 1485;
				$group = Group::findOrFail($id);

				$membership = $group->members()->where('userid', '=', $user->id)->get()->first();

				//if (!in_array($user->id, $group->members->pluck('userid')->toArray()))
				if (!$membership)
				{
					$found = false;
					$queues = $group->queues()
						//->withTrashed()
						->get();

					foreach ($queues as $queue)
					{
						$membership = $queue->users()->where('userid', '=', $user->id)->get()->first();

						if ($membership) //$queue->users()->where('userid', '=', $user->id)->count())
						{
							$found = true;
							break;
						}
					}

					if (!$found && !(auth()->user() && auth()->user()->can('manage groups')))
					{
						abort(404);
					}
				}

				app('pathway')
					->append(
						$group->name,
						route('site.users.account.section.show', array_merge($r, ['id' => $id]))
					);

				event($e = new GroupDisplay($group, 'details'));
				$sections = collect($e->getSections());

				$content = view('groups::site.group', [
					'user'  => $user,
					'group' => $group,
					'membership' => $membership,
					'sections' => $sections,
				]);
			}
			else
			{
				$groups = $user->groups()
					->whereIsManager()
					->where('groupid', '>', 0)
					->get();

				$content = view('groups::site.groups', [
					'user'   => $user,
					'groups' => $groups
				]);
			}
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('groups::groups.my groups') . ' <span class="badge">' . $total . '</span>',
			($event->getActive() == 'groups'),
			$content
		);
	}
}
