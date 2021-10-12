<?php
namespace App\Listeners\Users\Groups;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Events\GroupDisplay;
use App\Modules\Users\Events\UserNotifying;
use App\Modules\Users\Entities\Notification;

/**
 * User listener for Groups
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
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
		$events->listen(UserNotifying::class, self::class . '@handleUserNotifying');
	}

	/**
	 * Gather data for a user
	 *
	 * @param   UserBeforeDisplay  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$user = $event->getUser();

		// Owner
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->ownerofgroups = $memberships;

		$memberships = Member::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priorownerofgroups = $memberships;

		// Members
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->memberofgroups = $memberships;

		$memberships = Member::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priormemberofgroups = $memberships;

		// Viewers
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->viewerofgroups = $memberships;

		$memberships = Member::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priorviewerofgroups = $memberships;

		$memberships = UnixGroupMember::query()
			->where('userid', '=', $user->id)
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.unixgroups.read', ['id' => $membership->unixgroupid]);
		}

		$user->memberofunixgroups = $memberships;

		$memberships = UnixGroupMember::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.unixgroups.read', ['id' => $membership->unixgroupid]);
		}

		$user->priormemberofunixgroups = $memberships;

		$event->setUser($user);
	}

	/**
	 * Display data for a user
	 *
	 * @param   UserDisplay  $event
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
		$queueusers = $user->queues()
			->withTrashed()
			->whereIsActive()
			->whereIn('membertype', [1, 4])
			->get();

		foreach ($queueusers as $qu)
		{
			if ($qu->isMember() && $qu->isTrashed())
			{
				continue;
			}

			$queue = $qu->queue;

			if (!$queue || $queue->isTrashed())
			{
				continue;
			}

			if (!$queue->scheduler || $queue->scheduler->isTrashed())
			{
				continue;
			}

			if (!in_array($queue->groupid, $groups))
			{
				$groups[] = $queue->groupid;
				$total++;
			}
		}

		$unixusers = UnixGroupMember::query()
			->where('userid', '=', $user->id)
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($unixusers as $uu)
		{
			if ($uu->trashed())
			{
				continue;
			}

			$unixgroup = $uu->unixgroup;

			if (!$unixgroup || $unixgroup->trashed())
			{
				continue;
			}

			if (!$unixgroup->group)
			{
				continue;
			}

			if (!in_array($unixgroup->groupid, $groups))
			{
				$groups[] = $unixgroup->groupid;
				$total++;
			}
		}

		if ($event->getActive() == 'groups' || app('isAdmin'))
		{
			if (!app('isAdmin'))
			{
				app('pathway')
					->append(
						trans('groups::groups.my groups'),
						route('site.users.account.section', $r)
					);
			}

			if (!app('isAdmin') && $id = request()->segment(3))
			{
				$group = Group::findOrFail($id);

				$membership = $group->members()
					->where('userid', '=', $user->id)
					->orderBy('membertype', 'desc')
					->get()
					->first();

				if (!$membership)
				{
					$found = false;
					$queues = $group->queues()
						->withTrashed()
						->whereIsActive()
						->get();

					foreach ($queues as $queue)
					{
						$membership = $queue->users()
							->where('userid', '=', $user->id)
							->get()
							->first();

						if ($membership)
						{
							$found = true;
							break;
						}
					}

					if (!$found)
					{
						foreach ($unixusers as $membership)
						{
							if ($membership->trashed())
							{
								continue;
							}

							$unixgroup = $membership->unixgroup;

							if (!$unixgroup || $unixgroup->trashed())
							{
								continue;
							}

							if (!$unixgroup->group)
							{
								continue;
							}

							if ($unixgroup->groupid == $group->id)
							{
								$found = true;
								break;
							}
						}
					}

					//$found = in_array($id, $groups);

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

				$subsection = request()->segment(4);
				$subsection = $subsection ?: 'overview';

				event($e = new GroupDisplay($group, $subsection));
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
				$rows = $user->groups()
					->where('groupid', '>', 0)
					->orderBy('membertype', 'desc')
					->get();

				$groups = array_unique($rows->pluck('groupid')->toArray());

				foreach ($queueusers as $qu)
				{
					if ($qu->isMember() && $qu->isTrashed())
					{
						continue;
					}

					$queue = $qu->queue;

					if (!$queue || $queue->isTrashed())
					{
						continue;
					}

					if (!$queue->scheduler || $queue->scheduler->isTrashed())
					{
						continue;
					}

					if (!in_array($queue->groupid, $groups))
					{
						$qu->groupid = $queue->groupid;

						$rows->add($qu);

						$groups[] = $queue->groupid;
					}
				}

				foreach ($unixusers as $uu)
				{
					if ($uu->trashed())
					{
						continue;
					}

					$unixgroup = $uu->unixgroup;

					if (!$unixgroup || $unixgroup->trashed())
					{
						continue;
					}

					if (!$unixgroup->group)
					{
						continue;
					}

					if (!in_array($unixgroup->groupid, $groups))
					{
						$uu->groupid = $unixgroup->groupid;
						$uu->group = $unixgroup->group;
						$rows->add($uu);
						$groups[] = $unixgroup->groupid;
					}
				}

				$managers = $rows->filter(function($value, $key)
				{
					return $value->isManager();
				});//->pluck('groupid')->toArray();

				foreach ($rows as $k => $g)
				{
					foreach ($managers as $manager)
					{
						if ($g->groupid == $manager->groupid && $g->id != $manager->id)
						{
							$rows->forget($k);
						}
					}
				}

				$content = view('groups::' . (app('isAdmin') ? 'admin.groups.user' : 'site.groups'), [
					'user'   => $user,
					'groups' => $rows
				]);
			}
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('groups::groups.my groups') . (app('isAdmin') ? ' (' . $total . ')' : ' <span class="badge pull-right">' . $total . '</span>'),
			($event->getActive() == 'groups'),
			$content
		);
	}

	/**
	 * Display data for a user
	 *
	 * @param   UserNotifying  $event
	 * @return  void
	 */
	public function handleUserNotifying(UserNotifying $event)
	{
		$user = $event->user;

		// Owner
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$group = $membership->group;

			$total = $group->pendingMembersCount;

			if (!$total)
			{
				continue;
			}

			$title = trans('groups::groups.groups');

			$content = '<a href="' . route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'members']) . '">' . trans('groups::groups.group has pending requests', ['group' => $group->name]) . '</a>';

			$level = 'normal';

			$event->addNotification(new Notification($title, $content, $level));
		}
	}
}
