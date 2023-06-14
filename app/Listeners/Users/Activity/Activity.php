<?php
namespace App\Listeners\Users\Activity;

use Illuminate\Support\Fluent;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\History\Models\Log;
use App\Modules\Listeners\Models\Listener;
use Module;

/**
 * User listener for history
 */
class Activity
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
			->where('element', '=', 'Activity')
			->first();

		if (!$listener)
		{
			return;
		}

		if (auth()->user() && !in_array($listener->access, auth()->user()->getAuthorisedViewLevels()))
		{
			return;
		}

		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'activity'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		app('translator')->addNamespace(
			'listener.users.activity',
			__DIR__ . '/lang'
		);

		if ($event->getActive() == 'activity' || app('isAdmin'))
		{
			if (!app('isAdmin'))
			{
				app('pathway')
					->append(
						trans('listener.users.activity::activity.activity'),
						route('site.users.account.section', $r)
					);
			}

			$filters = array(
				'limit'     => config('list_limit', 20),
				'page'      => request()->input('page', 1),
				'order'     => Log::$orderBy,
				'order_dir' => Log::$orderDir,
				'status'    => request()->input('status'),
				'action'    => request()->input('action'),
			);

			$targetuserid = (auth()->user() ? auth()->user()->id : 0);

			$query = Log::query()
				/*->where(function($where) use ($user)
				{
					$where->where('targetuserid', '=', $user->id)
						->orWhere('userid', '=', $user->id);
						->orWhere(function($w) use ($user)
						{
							$w->where('targetuserid', '<', 0)
								->where('uri', '=', $user->email);
						});
				})*/
				->where('targetuserid', '=', $user->id);

			if ($filters['status'] == 'success')
			{
				$query->where('status', '<', 400);
			}

			if ($filters['status'] == 'error')
			{
				$query->where('status', '>=', 400);
			}

			if ($filters['action'] == 'emailed')
			{
				$query->where('app', '=', 'email');
			}

			if ($filters['action'] == 'created')
			{
				$query->where('transportmethod', '=', 'POST');
			}
			elseif ($filters['action'] == 'updated')
			{
				$query->where('transportmethod', '=', 'PUT');
			}
			elseif ($filters['action'] == 'deleted')
			{
				$query->where('transportmethod', '=', 'DELETE');
			}
			else
			{
				$query->where('transportmethod', '!=', 'GET');
			}

			$history = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->paginate($filters['limit'], ['*'], 'page', $filters['page']);

			app('view')->addNamespace(
				'listener.users.activity',
				__DIR__ . '/views'
			);

			$content = view('listener.users.activity::index', [
				'user'    => $user,
				'history' => $history,
				'filters' => $filters,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('listener.users.activity::activity.activity'),
			($event->getActive() == 'activity'),
			$content
		);
	}
}
