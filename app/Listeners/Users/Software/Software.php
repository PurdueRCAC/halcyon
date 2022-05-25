<?php
namespace App\Listeners\Users\Software;

use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Events\UnixGroupMemberCreating;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Events\ResourceMemberCreated;

/**
 * User listener for Software
 */
class Software
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
		$events->listen(UnixGroupMemberCreating::class, self::class . '@handleUnixGroupMemberCreating');
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

		$content = null;
		$user = $event->getUser();

		if (!$user->enabled)
		{
			return;
		}

		$r = ['section' => 'software'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		app('translator')->addNamespace(
			'listener.users.software',
			__DIR__ . '/lang'
		);

		if ($event->getActive() == 'software')
		{
			app('pathway')
				->append(
					trans('listener.users.software::software.title'),
					route('site.users.account.section', $r)
				);

			$unixgroups = UnixGroupMember::query()
				->where('userid', '=', $user->id)
				->get();

			app('view')->addNamespace(
				'listener.users.software',
				__DIR__ . '/views'
			);

			$content = view('listener.users.software::profile', [
				'user' => $user,
				'unixgroups' => $unixgroups,
				'software' => self::software()
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('listener.users.software::software.title'),
			($event->getActive() == 'software'),
			$content
		);
	}

	/**
	 * Check if the unix group is one of the software groups and the user is allowed to be added
	 *
	 * @param   UnixGroupMemberCreating  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreating(UnixGroupMemberCreating $event)
	{
		$row = $event->member;

		foreach (self::software() as $s)
		{
			if ($s['groupid'] == $row->unixgroupid)
			{
				event($ev = new UserBeforeDisplay($row->user));

				$user = $ev->getUser();

				if (in_array(strtolower($user->department), $s['dept_lower'])
				 || in_array(strtolower($user->school), $s['dept_lower']))
				{
					$event->restricted = false;
				}
				else
				{
					$asset = Asset::query()
						->where('rolename', '=', 'HPSSUSER')
						->first();

					event($e = new ResourceMemberCreated($asset, $user));
				}
				break;
			}
		}
	}

	/**
	 * Definitions for software access request page (tecplot, comsol, etc)
	 *
	 * List of departments that are in College of Engineering
	 * This list is generated from ldapsearch and the department field exactly as shown.
	 * This was filtered by hand so there may be missing items.
	 *
	 * @return  array
	 */
	public static function software()
	{
		$engineering = array(
			'Aeronautics and Astronautics',
			'Agricultural and Biological Engineering',
			'Biomedical Engineering',
			'Chemical Engineering',
			'Civil and Mechanical Engineering',
			'Civil Engineering',
			'College of Engineering and Sciences',
			'College of Engr Admin and Engr Exp Sta',
			'Div of Construction Engineering and Mgmt',
			'Electrical and Computer Eng',
			'Electrical and Computer Engineering',
			'Engineering Computer Network',
			'Engineering Education',
			'Industrial Engineering',
			'Materials Engineering',
			'Mechanical and Civil Engineering',
			'Mechanical Engineering',
			'Network for Computational Nanotechnology',
			'Nuclear Engineering',
			'Women In Engineering',
		);

		$software = array(
			array(
				'name'   => 'Comsol',
				'req'    => 'College of Engineering',
				'group'  => 'comsol',
				'groupid' => 0,
				'dept'   => $engineering,
				'dept_lower' => array_map('strtolower', $engineering),
				'access' => false  // Does current user have access? Just leave false, we'll figure it out later.
			),
			array(
				'name'   => 'Tecplot',
				'req'    => 'College of Engineering',
				'group'  => 'tecplot',
				'groupid' => 0,
				'dept'   => $engineering,
				'dept_lower' => array_map('strtolower', $engineering),
				'access' => false
			),
		);

		foreach ($software as $i => $s)
		{
			$group = UnixGroup::findByLongname($s['group']);

			if ($group)
			{
				$software[$i]['groupid'] = $group->id;
			}
		}

		return $software;
	}
}
