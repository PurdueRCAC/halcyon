<?php

namespace App\Halcyon\Form\Rules;

use App\Modules\Users\Models\User;
use App\Halcyon\Form\Rule;

/**
 * Form Rule class for usernames.
 */
class Username extends Rule
{
	/**
	 * @inheritdoc
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		$duplicate = User::query()
			->where('username', '=', $value)
			//->where('id', '<>', (int) $userId)
			->count();

		if ($duplicate)
		{
			return false;
		}

		return true;
	}
}
