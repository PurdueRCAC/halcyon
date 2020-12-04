<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\News;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Association;
use App\Modules\News\Models\Type;

/**
 * User listener for sessions
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
				->where($a . '.newstypeid', '=', $type->id)
				->whereIn($a . '.published', $states)
				->count();

			if ($event->getActive() == $type->alias)
			{
				app('pathway')
					->append(
						$type->name,
						route('site.users.account.section', $r)
					);

				$rows = Article::query()
					->select($a . '.*')
					->join($u, $u . '.newsid', $a . '.id')
					->where($u . '.associd', '=', $user->id)
					->where($u . '.assoctype', '=', 'user')
					->where($a . '.newstypeid', '=', $type->id)
					->whereIn($a . '.published', $states)
					->orderBy($a . '.datetimecreated', 'desc')
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
