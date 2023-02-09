<?php
namespace App\Widgets\UserList;

use App\Halcyon\Access\Map;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Widgets\Entities\Widget;
use Carbon\Carbon;
use App\Modules\Users\Events\UserBeforeDisplay;

/**
 * Widget for displaying a list of users
 */
class UserList extends Widget
{
	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$segments = request()->segments();
		$last = end($segments);

		if ($last)
		{
			$user = User::findByUsername($last);

			if ($user && $user->id)
			{
				$user->title = $user->facet('title');
				$user->specialty = $user->facet('specialty');
				$user->office = $user->facet('office');
				$user->phone = $user->facet('phone');
				$user->bio = $user->facet('bio');
				$user->thumb = asset('files/staff_thumb.png');

				if (file_exists(storage_path('app/public/users/' . $user->username . '/photo.jpg')))
				{
					$user->thumb = asset('files/users/' . $user->username . '/photo.jpg');
				}

				return view($this->getViewName('profile'), [
					'user'  => $user,
					'params' => $this->params,
				]);
			}
		}

		$a = (new User)->getTable();
		$u = (new UserUsername)->getTable();
		$b = (new Map)->getTable();

		$query = User::query()
			->select($a . '.*')
			->join($u, $u . '.userid', $a . '.id')
			->with('roles')
			->with('facets');

		$roles = $this->params->get('role_id');
		if (!empty($roles))
		{
			$query
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->whereIn($b . '.role_id', (array)$roles);
		}

		if ($search = $this->params->get('search'))
		{
			$query->where(function($where) use ($search, $a, $u)
			{
				$where->where($a . '.name', 'like', '%' . strtolower((string)$search) . '%')
					->orWhere($u . '.username', 'like', '%' . strtolower((string)$search) . '%');
			});
		}

		if ($created_at = $this->params->get('created_at'))
		{
			$query->where($u . '.datecreated', '>=', $created_at);
		}

		$state = $this->params->get('state', 'enabled');

		if ($state == 'enabled')
		{
			$query->whereNull($u . '.dateremoved');
		}
		elseif ($state == 'disabled')
		{
			$query->whereNotNull($u . '.dateremoved');
		}

		// Apply the range filter.
		if ($range = $this->params->get('range'))
		{
			$dNow = Carbon::now();
			$dStart = clone $dNow;

			switch ($range)
			{
				case 'past_week':
					$dStart->modify('-7 day');
					break;

				case 'past_1month':
					$dStart->modify('-1 month');
					break;

				case 'past_3month':
					$dStart->modify('-3 month');
					break;

				case 'past_6month':
					$dStart->modify('-6 month');
					break;

				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Reset the start time to be the beginning of today, local time.
					$dStart->setTime(0, 0, 0);
					break;
			}

			if ($range == 'past_year')
			{
				$query->where($u . '.datecreated', '<', $dStart->format('Y-m-d H:i:s'));
			}
			else
			{
				$query->where($u . '.datecreated', '>=', $dStart->format('Y-m-d H:i:s'));
				$query->where($u . '.datecreated', '<=', $dNow->format('Y-m-d H:i:s'));
			}
		}

		$users = $query
			->orderBy($a . '.' . (string)$this->params->get('order', 'name'), (string)$this->params->get('order_dir', 'asc'))
			->limit($this->params->get('limit', 5))
			->get();

		$users->each(function($user, $key) use ($segments)
		{
			//event($e = new UserBeforeDisplay($user));

			$user->title = $user->facet('title');
			$user->specialty = $user->facet('specialty');
			$user->office = $user->facet('office');
			$user->phone = $user->facet('phone');
			$user->thumb = asset('files/staff_thumb.png');

			if (file_exists(storage_path('app/public/users/' . $user->username . '/photo.jpg')))
			{
				$user->thumb = asset('files/users/' . $user->username . '/photo.jpg');
			}

			$user->page = route('page', ['uri' => implode('/', $segments) . '/' . $user->username]);
		});

		$layout = (string)$this->params->get('layout', 'index');

		return view($this->getViewName($layout), [
			'users'  => $users,
			'params' => $this->params,
		]);
	}
}
