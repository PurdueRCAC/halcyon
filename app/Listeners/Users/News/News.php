<?php
namespace App\Listeners\Users\News;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Association;
use App\Modules\News\Models\Type;
use App\Modules\Listeners\Models\Listener;
use App\Modules\Users\Events\UserNotifying;
use App\Modules\Users\Entities\Notification;
use Carbon\Carbon;

/**
 * User listener for News
 */
class News
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
		$events->listen(UserNotifying::class, self::class . '@handleUserNotifying');
	}

	/**
	 * Display data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		if (app('isAdmin'))
		{
			return;
		}

		$listener = Listener::query()
			->where('type', '=', 'listener')
			->where('folder', '=', 'users')
			->where('element', '=', 'News')
			->first();

		if (auth()->user() && !in_array($listener->access, auth()->user()->getAuthorisedViewLevels()))
		{
			return;
		}

		$content = null;
		$user = $event->getUser();
		$states = [1];
		if (auth()->user()->can('manage news'))
		{
			$states = [0, 1];
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->where('tagusers', '=', 1)
			->get();

		foreach ($types as $type)
		{
			$r = ['section' => $type->alias];
			if (auth()->user()->id != $user->id)
			{
				$r['u'] = $user->id;
			}

			$a = (new Article)->getTable();
			$u = (new Association)->getTable();

			$total = Article::query()
				->select($a . '.*')
				->join($u, $u . '.newsid', $a . '.id')
				->where($u . '.associd', '=', $user->id)
				->where($u . '.assoctype', '=', 'user')
				->whereNull($u . '.datetimeremoved')
				->where($a . '.newstypeid', '=', $type->id)
				->whereIn($a . '.published', $states)
				->count();

			if ($event->getActive() == $type->alias)
			{
				if (!app('isAdmin'))
				{
					app('pathway')
						->append(
							$type->name,
							route('site.users.account.section', $r)
						);
				}

				$rows = Article::query()
					->select($a . '.*', $u . '.id AS attending')
					->join($u, $u . '.newsid', $a . '.id')
					->where($u . '.associd', '=', $user->id)
					->where($u . '.assoctype', '=', 'user')
					->whereNull($u . '.datetimeremoved')
					->where($a . '.newstypeid', '=', $type->id)
					->whereIn($a . '.published', $states)
					->orderBy($a . '.datetimenews', 'desc')
					->paginate(config('list_limit', 20));

				$content = view('news::site.profile', [
					'user' => $user,
					'rows' => $rows,
					'type' => $type,
				]);
			}

			$event->addSection(
				route('site.users.account.section', $r),
				$type->name . ' <span class="badge pull-right">' . $total . '</span>',
				($event->getActive() == $type->alias),
				$content
			);
		}
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

		$a = (new Article)->getTable();
		$u = (new Association)->getTable();
		$now = Carbon::now();

		$rows = Article::query()
			->select($a . '.*', $u . '.id AS attending')
			->join($u, $u . '.newsid', $a . '.id')
			->where($u . '.associd', '=', $user->id)
			->where($u . '.assoctype', '=', 'user')
			->whereNull($u . '.datetimeremoved')
			->where(function($where) use ($now, $a)
			{
				$where->where($a . '.datetimenews', '>=', $now->toDateTimeString())
					->orWhere(function($wh) use ($now, $a)
					{
						$wh->whereNull($a . '.datetimenewsend')
							->orWhere($a . '.datetimenewsend', '>', $now->toDateTimeString());
					});
			})
			->whereIn($a . '.published', [1])
			->orderBy($a . '.datetimenews', 'desc')
			->get();

		foreach ($rows as $row)
		{
			$title = $row->type->name;

			$content = '<a href="' . route('site.news.show', ['id' => $row->id]) . '">' . trans('news::news.upcoming event at', ['type' => $row->type->name, 'time' => $row->formatDate($row->datetimenews, $row->datetimenewsend)]) . '</a>';

			$level = 'normal';
			if ($row->isNow())
			{
				$level = 'high';
			}

			$event->addNotification(new Notification($title, $content, $level));
		}
	}
}
