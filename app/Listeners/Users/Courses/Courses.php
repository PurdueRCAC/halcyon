<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\Courses;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Courses\Models\Account;

/**
 * User listener for sessions
 */
class Courses
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

		$r = ['section' => 'class'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		if ($event->getActive() == 'class')
		{
			app('pathway')
				->append(
					trans('courses::courses.my courses'),
					route('site.users.account.section', $r)
				);

			if ($id = request()->segment(3))
			{
				$group = Group::findOrFail($id);

				app('pathway')
					->append(
						$group->name,
						route('site.users.account.section', array_merge($r, ['id' => $id]))
					);

				$content = view('courses::site.group', [
					'user'  => $user,
					'group' => $group,
				]);
			}
			else
			{
				$courses = Account::query()
					->where('userid', '=', $user->id)
					->get();

				$content = view('courses::site.profile', [
					'user'   => $user,
					'courses' => $courses
				]);
			}
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('courses::courses.my courses'),
			($event->getActive() == 'class'),
			$content
		);
	}
}
