<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Members;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Users\Models\User;

/**
 * Module class for user data
 */
class Members extends Widget
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

		$data = [
			'widget' => $this->model
		];

		$data['unconfirmed'] = User::query()
			->where('block', '=', 0)
			->where('activation', '<', 1)
			->count();

		$data['confirmed'] = User::query()
			->where('block', '=', 0)
			->where('activation', '>', 0)
			->count();

		$data['pastDay'] = User::query()
			->where('block', '=', 0)
			->where('created_at', '>=', gmdate('Y-m-d', (time() - 24*3600)) . ' 00:00:00')
			->count();

		$data['approved'] = User::query()
			->where('block', '=', 0)
			->where('activation', '>', 0)
			//->where('approved', '>', 0)
			->count();

		$data['unapproved'] = User::query()
			->where('block', '=', 0)
			->where('activation', '>', 0)
			//->where('approved', '=', 0)
			->count();

		return view($this->getViewName('index'), $data);
	}
}
