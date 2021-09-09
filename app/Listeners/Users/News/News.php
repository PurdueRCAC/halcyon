<?php
namespace App\Listeners\Users\News;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Association;
use App\Modules\News\Models\Type;
use App\Modules\Listeners\Models\Listener;

/**
 * User listener for News
 */
class News
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
		if (app('isAdmin'))
		{
			return;
		}

		$listener = Listener::query()
			->where('type', '=', 'listener')
			->where('folder', '=', 'users')
			->where('element', '=', 'News')
			->get()
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
				->where(function($where) use ($u)
				{
					$where->whereNull($u . '.datetimeremoved')
						->orWhere($u . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
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
					->where(function($where) use ($u)
					{
						$where->whereNull($u . '.datetimeremoved')
							->orWhere($u . '.datetimeremoved', '=', '0000-00-00 00:00:00');
					})
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
}
