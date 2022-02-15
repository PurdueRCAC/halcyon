<?php
namespace App\Widgets\Groups;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Groups\Models\Group;

/**
 * Module class for Group data
 */
class Groups extends Widget
{
	/**
	 * Display widget contents
	 *
	 * @return  void
	 */
	public function run()
	{
		if (!app('isAdmin'))
		{
			return;
		}

		$groups = Group::query()
			->withCount('members')
			->orderBy('datetimecreated', 'desc')
			->limit($this->params->get('limit', 10))
			->get();

		return view($this->getViewName('index'), [
			'groups' => $groups,
			'widget' => $this->model,
		]);
	}
}
