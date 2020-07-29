<?php

namespace App\Modules\Courses\Composers;

use Illuminate\Contracts\View\View;
use App\Modules\Users\Models\User;

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
		//$sections['/courses'] = 'My Cool Courses';

		$section = array(
			'route'   => route('site.users.account.section', ['section' => 'courses']),
			'name'    => trans('courses::courses.my courses'),
			'content' => '',
		);

		if (request()->segments(2) == 'courses'
		 || request()->segments(2) == 'classes')
		{
			$section['content'] = view('courses::site.profile', [
				'user' => $this->user,
			]);
		}

		$sections[] = $section;

		$view->with('sections', $sections);
	}
}
