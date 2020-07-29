<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\ContactReports;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\User;

/**
 * User listener for sessions
 */
class ContactReports
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

		$r = ['section' => 'contactreports'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		$p = (new Report)->getTable();
		$u = (new User)->getTable();

		$total = Report::query()
			->select($p . '.*')
			->join($u, $u . '.contactreportid', $p . '.id')
			->where($u . '.userid', '=', $user->id)
			->count();

		if ($event->getActive() == 'contactreports')
		{
			app('pathway')
				->append(
					trans('contactreports::contactreports.contact reports'),
					route('site.users.account.section', $r)
				);

			//$r = (new Report)->getTable();
			//$u = (new User)->getTable();

			$reports = Report::query()
				->select($p . '.*')
				->join($u, $u . '.contactreportid', $p . '.id')
				->where($u . '.userid', '=', $user->id)
				->orderBy($p . '.datetimecreated', 'desc')
				->paginate(config('list_limit', 20));

			$content = view('contactreports::site.profile', [
				'user'    => $user,
				'reports' => $reports,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('contactreports::contactreports.contact reports') . ' <span class="badge">' . $total . '</span>',
			($event->getActive() == 'contactreports'),
			$content
		);
	}
}
