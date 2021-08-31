<?php
namespace App\Listeners\Users\Courses;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Courses\Events\InstructorLookup;
use App\Modules\Resources\Models\Asset;
use Carbon\Carbon;

/**
 * User listener for Courses
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
	 * @param   UserDisplay  $event
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

		$total = Account::query()
			->where('userid', '=', $user->id)
			->where('datetimestop', '>', Carbon::now()->toDateTimeString())
			->count();

		if (app('isAdmin'))
		{
			$now = Carbon::now();

			$instructing = Account::query()
				->where('userid', '=', $user->id)
				->where('datetimestop', '>', $now->toDateTimeString())
				->orderBy('datetimestart', 'desc')
				->get();

			$query = Member::query()
				->where('userid', '=', $user->id);

			if (count($instructing))
			{
				$query->whereNotIn('classaccountid', $instructing->pluck('id')->toArray());
			}

			$student = $query
				->where('datetimestop', '>', $now->toDateTimeString())
				->orderBy('datetimestart', 'desc')
				->get();

			$total += count($student);

			$content = view('courses::admin.profile', [
				'user'    => $user,
				'instructing' => $instructing,
				'student' => $student,
			]);
		}
		else
		{
			if ($event->getActive() == 'class')
			{
				app('pathway')
					->append(
						trans('courses::courses.my courses'),
						route('site.users.account.section', $r)
					);

				event($e = new InstructorLookup($user, false));

				$classes = $e->courses;
				$resources = Asset::query()
					->where('listname', '=', 'scholar')
					->orderBy('name')
					->get();

				$courses = Account::query()
					->where('userid', '=', $user->id)
					->where('datetimestop', '>', Carbon::now()->toDateTimeString())
					->orderBy('datetimestart', 'desc')
					->get();

				$content = view('courses::site.profile', [
					'user'    => $user,
					'courses' => $courses,
					'classes' => $classes,
					'resources' => $resources,
				]);
			}
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('courses::courses.my courses') . (app('isAdmin') ? ' (' . $total . ')' : ' <span class="badge pull-right">' . $total . '</span>'),
			($event->getActive() == 'class'),
			$content
		);
	}
}
