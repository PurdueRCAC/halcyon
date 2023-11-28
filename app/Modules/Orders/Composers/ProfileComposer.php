<?php

namespace App\Modules\Orders\Composers;

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

		$section = array(
			'route'   => route('site.users.account.section', ['section' => 'orders']),
			'name'    => trans('orders::orders.my orders'),
			'content' => '',
		);

		if (request()->segment(2) == 'orders')
		{
			$section['content'] = view('orders::site.profile', [
				'user' => $this->user,
			]);
		}

		$sections[] = $section;

		$view->with('sections', $sections);
	}
}
