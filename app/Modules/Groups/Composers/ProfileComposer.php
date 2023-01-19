<?php

namespace App\Modules\Groups\Composers;

use Illuminate\Contracts\View\View;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

class ProfileComposer
{
	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @param  User  $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @param  View  $view
	 * @return  void
	 */
	public function compose(View $view)
	{
		$sections = (array)$view->sections;

		$section = array(
			'route'   => route('site.users.account.section', ['section' => 'groups']),
			'name'    => trans('groups::groups.my groups'),
			'content' => '',
		);

		if (request()->segment(2) == 'groups')
		{
			app('pathway')
				->append(
					trans('groups::groups.my groups'),
					route('site.users.account.section', ['section' => 'groups'])
				);

			if ($id = request()->segment(3))
			{
				$group = Group::findOrFail($id);

				$section['content'] = view('groups::site.group', [
					'user' => $this->user,
					'group' => $group,
				]);
			}
			else
			{
				$groups = $this->user->groups()
					->whereIsManager()
					->where('groupid', '>', 0)
					->get();

				$section['content'] = view('groups::site.groups', [
					'user' => $this->user,
					'groups' => $groups
				]);
			}
		}

		$sections[] = $section;

		$view->with('sections', $sections);
	}
}
