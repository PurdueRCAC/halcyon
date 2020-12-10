<?php
namespace App\Widgets\Userlist;

use App\Halcyon\Access\Map;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Widgets\Entities\Widget;
use Carbon\Carbon;

/**
 * Widget for displaying a list of users
 */
class Userlist extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$a = (new User)->getTable();
		$u = (new UserUsername)->getTable();
		$b = (new Map)->getTable();

		$query = User::query()
			->select($a . '.*')
			->join($u, $u . '.userid', $a . '.id')
			->with('roles');

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
			$query->where(function($where) use ($u)
			{
				$where->whereNull($u . '.dateremoved')
					->orWhere($u . '.dateremoved', '=', '0000-00-00 00:00:00');
			});
		}
		elseif ($state == 'disabled')
		{
			$query->where(function($where) use ($u)
			{
				$where->whereNotNull($u . '.dateremoved')
					->where($u . '.dateremoved', '!', '0000-00-00 00:00:00');
			});
		}

		// Apply the range filter.
		if ($range = $this->params->get('range'))
		{
			// Get UTC for now.
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

				case 'post_year':
				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Reset the start time to be the beginning of today, local time.
					$dStart->setTime(0, 0, 0);
					break;
			}

			if ($filters['range'] == 'post_year')
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
			->orderBy($a . '.' . $this->params->get('order', 'name'), $this->params->get('order_dir', 'asc'))
			->limit($this->params->get('limit', 5))
			->get();

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'users'  => $users,
			'params' => $this->params,
		]);
	}
}
