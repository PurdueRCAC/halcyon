<?php
namespace App\Listeners\Users\History;

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

			$groups = $user->groups()
				->withTrashed()
				->orderBy('datecreated', 'desc')
				->get();

			$unixgroups = \App\Modules\Groups\Models\UnixGroupMember::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			$queues = \App\Modules\Queues\Models\User::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			$courses = \App\Modules\Courses\Models\Member::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			$classes = \App\Modules\Courses\Models\Account::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				->whereNotIn('id', $courses->pluck('classaccountid')->toArray())
				->orderBy('datetimecreated', 'desc')
				->get();

			foreach ($classes as $class)
			{
				$member = new \App\Modules\Courses\Models\Member;
				$member->classaccountid = $class->id;
				$member->userid = $class->userid;
				$member->datetimecreated = $class->datetimecreated;
				$member->datetimeremoved = $class->datetimeremoved;
				$member->datetimestart = $class->datetimestart;
				$member->datetimestop = $class->datetimestop;
				$member->membertype = 2;

				$courses->push($member);
			}

			$courses = $courses->sortByDesc('datetimecreated');

			$content = view('history::site.profile', [
				'user'    => $user,
				'groups'  => $groups,
				'unixgroups'  => $unixgroups,
				'queues'  => $queues,
				'courses' => $courses,
				//'history' => $history,
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
