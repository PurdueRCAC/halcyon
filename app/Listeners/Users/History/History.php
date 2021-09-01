<?php
namespace App\Listeners\Users\History;

use Illuminate\Support\Fluent;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\History\Models\Log;
use App\Modules\Listeners\Models\Listener;

/**
 * User listener for history
 */
class History
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
	 * Display data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$listener = Listener::query()
			->where('type', '=', 'listener')
			->where('folder', '=', 'users')
			->where('element', '=', 'History')
			->get()
			->first();

		if (auth()->user() && !in_array($listener->access, auth()->user()->getAuthorisedViewLevels()))
		{
			return;
		}

		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'history'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		if ($event->getActive() == 'history' || app('isAdmin'))
		{
			if (!app('isAdmin'))
			{
				app('pathway')
					->append(
						trans('history::history.history'),
						route('site.users.account.section', $r)
					);
			}

			/*$history = Log::query()
				->where('userid', '=', $user->id)
				->where('transportmethod', '!=', 'GET')
				->paginate(config('list_limit', 20));*/
			$items = collect([]);

			$groups = $user->groups()
				->withTrashed()
				->orderBy('datecreated', 'desc')
				->get();

			foreach ($groups as $g)
			{
				$group = $g->group;

				if (!$group)
				{
					// Record doesn't exist?
					continue;
				}

				$item = new Fluent;
				$item->id = $g->id;
				$item->route = route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $g->groupid, 'subsection' => 'members', 'u' => $user->id != auth()->user()->id ? $user->id : null]);
				$item->type = 'group';
				$item->subtype = ($g->type ? $g->type->name : trans('global.unknown'));
				$item->description = ($group ? $group->name : trans('global.unknown'));
				$item->created = $g->datecreated;
				$item->removed = $g->dateremoved;
				$item->isTrashed = $g->isTrashed();

				$items->push($item);
			}

			$unixgroups = \App\Modules\Groups\Models\UnixGroupMember::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			foreach ($unixgroups as $g)
			{
				$ug = $g->unixgroup()->withTrashed()->first();

				$item = new Fluent;
				$item->id = $g->id;
				$item->route = route('site.users.account.section.show', ['section' => 'groups', 'id' => $ug->groupid, 'u' => $user->id != auth()->user()->id ? $user->id : null]);
				$item->type = 'unix group';
				$item->description = $ug->longname . ' ' . ($ug && $ug->group ? '(' . $ug->group->name . ')' : trans('global.unknown'));
				$item->created = $g->datetimecreated;
				$item->removed = $g->datetimeremoved;
				$item->isTrashed = $g->isTrashed();

				$items->push($item);
			}

			$queues = \App\Modules\Queues\Models\User::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			foreach ($queues as $g)
			{
				$q = $g->queue()
					->withTrashed()
					->first();

				if (!$q)
				{
					// Record doesn't exist?
					continue;
				}

				$item = new Fluent;
				$item->id = $g->id;
				$item->route = route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $q->groupid, 'subsection' => 'queues', 'u' => $user->id != auth()->user()->id ? $user->id : null]);
				$item->type = 'queue';
				$item->description = $q->name . ' ' . ($q->resource ? '(' . $q->resource->name . ')' : trans('global.unknown'));
				$item->created = $g->datetimecreated;
				$item->removed = $g->datetimeremoved;
				$item->isTrashed = $g->isTrashed();

				if ($g->isTrashed())
				{
					$item->isTrashed = $g->isTrashed();
				}
				elseif ($q)
				{
					if ($q->isTrashed())
					{
						$item->isTrashed = $q->isTrashed();
						$item->removed = $q->datetimeremoved;
					}
					else
					{
						if ($q->resource && $q->resource->isTrashed())
						{
							$item->isTrashed = $q->resource->isTrashed();
							$item->removed = $q->resource->datetimeremoved;
						}
					}
				}

				$items->push($item);
			}

			$courses = \App\Modules\Courses\Models\Member::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			foreach ($courses as $g)
			{
				$class = $g->account()
					->withTrashed()
					->first();

				if (!$class)
				{
					// Record doesn't exist?
					continue;
				}

				$item = new Fluent;
				$item->id = $g->id;
				$item->route = route('site.users.account.section', ['section' => 'class', 'u' => $user->id != auth()->user()->id ? $user->id : null]);
				$item->type = 'class';
				if ($class->semester == 'Workshop')
				{
					$item->description = $class->classname;
				}
				else
				{
					$item->description = $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ')';
				}
				$item->created = $g->datetimecreated;
				$item->removed = $g->datetimeremoved;
				$item->isTrashed = $g->isTrashed();

				$items->push($item);
			}

			$classes = \App\Modules\Courses\Models\Account::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->whereNotIn('id', $courses->pluck('classaccountid')->toArray())
				->orderBy('datetimecreated', 'desc')
				->get();

			foreach ($classes as $class)
			{
				$item = new Fluent;
				$item->id = $class->id;
				$item->route = route('site.users.account.section', ['section' => 'class', 'u' => $user->id != auth()->user()->id ? $user->id : null]);
				$item->type = 'class';
				if ($class->semester == 'Workshop')
				{
					$item->description = $class->classname;
				}
				else
				{
					$item->description = $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ')';
				}
				$item->created = $class->datetimestart;
				$item->removed = $class->datetimestop;
				$item->isTrashed = $class->isTrashed();

				$items->push($item);
			}

			$history = $items->sortByDesc('created');

			$content = view('history::site.profile', [
				'user'    => $user,
				'history' => $history,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('history::history.history'),
			($event->getActive() == 'history'),
			$content
		);
	}
}
