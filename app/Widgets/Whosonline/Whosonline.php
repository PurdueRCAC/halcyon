<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Whosonline;

use App\Modules\Widgets\Entities\Widget;

/**
 * Widget class for displaying who is online
 */
class Whosonline extends Widget
{
	/**
	 * Display module contents
	 *
	 * @return  void
	 */
	public function run()
	{
		if (config('session.driver') != 'database')
		{
			return view($this->getViewName('error'));
		}

		if (app('isAdmin'))
		{
			return $this->admin();
		}

		return $this->site();
	}

	/**
	 * Display module contents
	 *
	 * @return  void
	 */
	private function site()
	{
		// Get all sessions
		$sessions = app('db')->newQuery()
			->table('sessions')
			->limit($this->params->get('limit', 25))
			->get();

		// Vars to hold guests & logged in members
		$guestCount    = 0;
		$loggedInCount = 0;
		$loggedInList  = array();

		// Get guest and logged in counts/list
		foreach ($sessions as $session)
		{
			if ($session->guest == 1)
			{
				$guestCount++;
			}
			else
			{
				$loggedInCount++;

				$user = User::findOrNew($session->userid);

				if ($user->id)
				{
					$loggedInList[] = $user;
				}
			}
		}

		// Render view
		return view($this->getViewName('index'), [
			'guestCount'    => $guestCount,
			'loggedInCount' => $loggedInCount,
			'loggedInList'  => $loggedInList,
			'params'        => $this->params
		]);
	}

	/**
	 * Display module contents for Admin
	 *
	 * @return  void
	 */
	private function admin()
	{
		// get active sessions (users online)
		$sessions = app('db')
			->table('sessions')
			->limit($this->params->get('limit', 25))
			->get();

		$siteUserCount  = 0;
		$adminUserCount = 0;
		$found = array();

		/*foreach ($sessions as $i => $row):
			if ($row->userid && in_array($row->client_id . '.' . $row->userid, $found)):
				unset($sessions[$i]);
				continue;
			endif;

			$found[] = $row->client_id . '.' . $row->userid;

			if ($row->client_id == 0):
				$siteUserCount++;
			else:
				$adminUserCount++;
			endif;
		endforeach;*/

		$editAuthorized = auth()->user()->can('manage users');

		// Get the view
		return view($this->getViewName('admin'), [
			'rows'   => $sessions,
			'widget' => $this->model,
			'siteUserCount' => $siteUserCount,
			'adminUserCount' => $adminUserCount,
			'editAuthorized' => $editAuthorized,
			'params'        => $this->params
		]);
	}
}
