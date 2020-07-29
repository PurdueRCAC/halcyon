<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\History;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\History\Models\Log;

/**
 * User listener for sessions
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
				->orderBy('datecreated', 'desc')
				->get();

			$queues = \App\Modules\Queues\Models\User::query()
				->where('userid', '=', $user->id)
				->orderBy('datetimecreated', 'desc')
				->get();

			$content = view('history::site.profile', [
				'user'    => $user,
				'groups'  => $groups,
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
