<?php
namespace App\Listeners\Users\History;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\History\Models\Log;

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
		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'history'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		if ($event->getActive() == 'history')
		{
			app('pathway')
				->append(
					trans('history::history.history'),
					route('site.users.account.section', $r)
				);

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
				/*->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})*/
				->orderBy('datetimecreated', 'desc')
				->get();

			$queues = \App\Modules\Queues\Models\User::query()
				->withTrashed()
				->where('userid', '=', $user->id)
				/*->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})*/
				->orderBy('datetimecreated', 'desc')
				->get();

			$content = view('history::site.profile', [
				'user'    => $user,
				'groups'  => $groups,
				'unixgroups'  => $unixgroups,
				'queues'  => $queues,
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
